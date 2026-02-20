<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ImportDmZpm017Request;
use App\Services\DmZpm017ImportService;

class DmSapImportController extends Controller
{
    public function importarZpm017(ImportDmZpm017Request $request, DmZpm017ImportService $service)
    {
        $file = $request->file('archivo');
        if (! $file) {
            return response()->json(['message' => 'Archivo no recibido.'], 422);
        }

        $result = $service->importFromXlsx(
            $file->getRealPath(),
            (string) $file->getClientOriginalName()
        );

        return response()->json([
            'message' => 'Importación ZPM017 completada.',
            'data' => $result,
        ]);
    }
}

