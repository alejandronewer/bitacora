<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Generator;
use RuntimeException;
use ZipArchive;

class XlsxWorkbookReader
{
    private const NS_SPREADSHEET = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_OFFICE_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_PACKAGE_REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private ZipArchive $zip;

    /** @var array<int, string> */
    private array $sharedStrings = [];

    /** @var array<int, array{name:string, id:string, path:string}> */
    private array $sheets = [];

    public function __construct(private readonly string $xlsxPath)
    {
        $zip = new ZipArchive();
        $openResult = $zip->open($xlsxPath);
        if ($openResult !== true) {
            throw new RuntimeException('No se pudo abrir el archivo XLSX.');
        }

        $this->zip = $zip;
        $this->loadWorkbookStructure();
        $this->loadSharedStrings();
    }

    public function __destruct()
    {
        if (isset($this->zip)) {
            $this->zip->close();
        }
    }

    /**
     * @return array<int, array{name:string, id:string, path:string}>
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * @return Generator<int, array{row:int, cells:array<int, string>}>
     */
    public function iterSheetRows(string $sheetPath): Generator
    {
        $sheetXml = $this->readXml($sheetPath);
        $sheetDoc = new DOMDocument();
        $sheetDoc->loadXML($sheetXml);

        $xpath = new DOMXPath($sheetDoc);
        $xpath->registerNamespace('x', self::NS_SPREADSHEET);

        $rows = $xpath->query('/x:worksheet/x:sheetData/x:row');
        if ($rows === false) {
            return;
        }

        $rowAuto = 0;
        foreach ($rows as $rowNode) {
            $rowAuto++;
            $rowNum = (int) ($rowNode->attributes?->getNamedItem('r')?->nodeValue ?? $rowAuto);

            $cells = [];
            $cellNodes = $xpath->query('./x:c', $rowNode);
            if ($cellNodes === false) {
                continue;
            }

            /** @var DOMElement $cellNode */
            foreach ($cellNodes as $cellNode) {
                $cellRef = (string) $cellNode->getAttribute('r');
                if ($cellRef === '') {
                    continue;
                }

                $colLetters = preg_replace('/\d+/', '', $cellRef) ?: '';
                if ($colLetters === '') {
                    continue;
                }

                $colIndex = self::columnToIndex($colLetters);
                $cells[$colIndex] = $this->decodeCellValue($xpath, $cellNode);
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);
            yield [
                'row' => $rowNum,
                'cells' => $cells,
            ];
        }
    }

    private function loadWorkbookStructure(): void
    {
        $workbookXml = $this->readXml('xl/workbook.xml');
        $relsXml = $this->readXml('xl/_rels/workbook.xml.rels');

        $workbookDoc = new DOMDocument();
        $workbookDoc->loadXML($workbookXml);
        $workbookXPath = new DOMXPath($workbookDoc);
        $workbookXPath->registerNamespace('x', self::NS_SPREADSHEET);
        $workbookXPath->registerNamespace('r', self::NS_OFFICE_REL);

        $relsDoc = new DOMDocument();
        $relsDoc->loadXML($relsXml);
        $relsXPath = new DOMXPath($relsDoc);
        $relsXPath->registerNamespace('p', self::NS_PACKAGE_REL);

        $ridToTarget = [];
        $relNodes = $relsXPath->query('/p:Relationships/p:Relationship');
        if ($relNodes !== false) {
            /** @var DOMElement $relNode */
            foreach ($relNodes as $relNode) {
                $type = (string) $relNode->getAttribute('Type');
                if (! str_ends_with($type, '/worksheet')) {
                    continue;
                }

                $rid = (string) $relNode->getAttribute('Id');
                $target = (string) $relNode->getAttribute('Target');
                if ($rid === '' || $target === '') {
                    continue;
                }

                $ridToTarget[$rid] = $this->sanitizeSheetTarget($target);
            }
        }

        $sheetNodes = $workbookXPath->query('/x:workbook/x:sheets/x:sheet');
        if ($sheetNodes === false) {
            return;
        }

        /** @var DOMElement $sheetNode */
        foreach ($sheetNodes as $sheetNode) {
            $rid = (string) $sheetNode->getAttributeNS(self::NS_OFFICE_REL, 'id');
            if ($rid === '') {
                $rid = (string) $sheetNode->getAttribute('r:id');
            }
            if ($rid === '' || ! isset($ridToTarget[$rid])) {
                continue;
            }

            $this->sheets[] = [
                'name' => (string) $sheetNode->getAttribute('name'),
                'id' => $rid,
                'path' => $ridToTarget[$rid],
            ];
        }
    }

    private function loadSharedStrings(): void
    {
        $entry = 'xl/sharedStrings.xml';
        if ($this->zip->locateName($entry) === false) {
            $this->sharedStrings = [];
            return;
        }

        $xml = $this->readXml($entry);
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('x', self::NS_SPREADSHEET);

        $siNodes = $xpath->query('/x:sst/x:si');
        if ($siNodes === false) {
            return;
        }

        /** @var DOMElement $siNode */
        foreach ($siNodes as $siNode) {
            $parts = [];
            $textNodes = $xpath->query('.//x:t', $siNode);
            if ($textNodes !== false) {
                foreach ($textNodes as $textNode) {
                    $parts[] = $textNode->nodeValue ?? '';
                }
            }
            $this->sharedStrings[] = implode('', $parts);
        }
    }

    private function decodeCellValue(DOMXPath $xpath, DOMElement $cellNode): string
    {
        $type = (string) $cellNode->getAttribute('t');

        if ($type === 's') {
            $sharedIndex = (int) $this->queryCellText($xpath, $cellNode, './x:v');
            return $this->sharedStrings[$sharedIndex] ?? '';
        }

        if ($type === 'inlineStr') {
            return $this->queryCellText($xpath, $cellNode, './x:is/x:t');
        }

        if ($type === 'b') {
            return $this->queryCellText($xpath, $cellNode, './x:v') === '1' ? '1' : '0';
        }

        return $this->queryCellText($xpath, $cellNode, './x:v');
    }

    private function queryCellText(DOMXPath $xpath, DOMElement $context, string $query): string
    {
        $nodes = $xpath->query($query, $context);
        if ($nodes === false || $nodes->length === 0) {
            return '';
        }

        return (string) ($nodes->item(0)?->nodeValue ?? '');
    }

    private function sanitizeSheetTarget(string $target): string
    {
        $target = str_replace('\\', '/', $target);
        return 'xl/' . ltrim($target, '/');
    }

    private function readXml(string $entry): string
    {
        $content = $this->zip->getFromName($entry);
        if ($content === false) {
            throw new RuntimeException("No se encontró el recurso XML {$entry} en el archivo XLSX.");
        }

        return $content;
    }

    private static function columnToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $charCode = ord($letters[$i]) - 64; // A=1..Z=26
            if ($charCode < 1 || $charCode > 26) {
                continue;
            }
            $index = ($index * 26) + $charCode;
        }

        return $index;
    }
}

