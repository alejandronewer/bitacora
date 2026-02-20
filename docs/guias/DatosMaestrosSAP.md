# Datos Maestros SAP-like (Ubicaciones Técnicas y Equipos)

## Objetivo
Determinar estructura de clases y objetos SAP/R3 para integrar información SAP-like (ubicaciones técnicas, equipos y clases) al modelo actual del proyecto.

Este documento sirve para:
1. Estudiar la homologación de datos.
2. Alinear extracción de datos al modelo del proyecto.

## Fuentes Analizadas
1. `docs/sap_r3/HOMOLOGACION COMUNICACIONES.pdf` (v2016).
2. `docs/sap_r3/PM_TRANSMISION.pdf` (Estructura de procesos PM).
3. `docs/sap_r3/equipos/ZPM017/2026/TODOS_ZPM017 _EQUIPOS_2026.01.19_MLH.xlsx` (fuente de datos principal).


## Modelo Actual Relevante
Tablas existentes para dominio SAP/R3:
1. `dm_ubicaciones_tecnicas`
2. `dm_equipos`
3. `dm_ubicacion_detalle_nivel`

## Diccionario Preliminar de Niveles (Ubicación Técnica)
Valores de homologación (documentación encontrada en SharePoint):

### Convención por nivel (definición de negocio)
1. `Nivel 1` = `ÁREA` (GRT / área regional).
2. `Nivel 2` = `SUBÁREA` (centro de trabajo / centro gestor).
3. `Nivel 3` = `TIPO DE SISTEMA PRINCIPAL` (Rama).
4. `Nivel 4` = `NOMBRE DE INSTALACIÓN` (se interpreta por rama).
5. `Nivel 5` = `TIPO DE SISTEMA SECUNDARIO`.
6. `Nivel 6` = `NOMENCLATURA` (nomenclatura del elemento de la rama/nivel 3).
7. `Nivel 7` = `TIPO DE EQUIPO (1ER NIVEL)` *(Encontré valores de 2 caracteres).
8. `Nivel 8` = `TIPO DE EQUIPO (2DO NIVEL)`.
9. La ubicación técnica se normaliza hasta `Nivel 8`; cualquier desglose adicional queda fuera de alcance operativo.

### Regla de ancho de caracteres por nivel (obligatoria)
1. Se respeta ancho máximo por nivel:
   - `Nivel 1`: 2 caracteres.
   - `Nivel 2`: 4 caracteres.
   - `Nivel 3`: 2 caracteres.
   - `Nivel 4`: 3 caracteres.
   - `Nivel 5`: 2 caracteres.
   - `Nivel 6`: 5 caracteres.
   - `Nivel 7`: 3 caracteres. *(Encontré valores de 2 caracteres).
   - `Nivel 8`: 2 caracteres.
2. Casos con guion bajo o guion interno dentro del valor no crean un nivel adicional. *(Solo ocurrió en nivel 6).
   - `13_KV` sigue siendo `Nivel 6` (5 caracteres), aunque visualmente tenga un guion adicional.
   - `T-020` sigue siendo `Nivel 6` (5 caracteres), aunque visualmente tenga un guion adicional.

### Nivel 1
1. `BC`: Gerencia Regional de Transmisión Baja California.
2. `DD`: EPS Distribución.
3. `PA`: Packard. *(Parece error de captura humano).
4. `RU`: Rumorosa. *(Parece error de captura humano).

### Nivel 2
1. `T010`: Sede Gerencia Regional de Transmisión Baja California (GRTBC).
2. `T011`: Zona de Transmisión Costa.
3. `T012`: Zona de Transmisión Valle.
4. `T013`: Zona de Transmisión Sur.

### Nivel 3
1. `LT`: Líneas de Transmisión.
2. `PG`: Plantas Generadoras.
3. `SE`: Subestaciones.
4. `TA`: Equipo Técnico-Administrativo.
5. `ZO`: Zona de Operación.

### Niveles posteriores (niveles 4+)
1. Para `Nivel 3 = SE`, el `Nivel 4` corresponde a acrónimo del sitio.
   - Ejemplo: `PJZ` = Presidente Juárez.
   - Ejemplo: `CHQ` = S.E. Chapultepec.
   - Para `SE`, el siguiente nivel observado (ej. `BC-3011-SE-CHQ-<nivel5>`) clasifica áreas/subconjuntos:
     - `BB`: Buses.
     - `CC`: Caseta de Confiabilidad.
     - `BC`: Bahías de compensación.
     - `BL`: Bahía de línea de transmisión.
     - `BM`: Bahía de líneas menores 230 kV.
     - `BS`: Bahías suplementarias.
     - `BT`: Bahías de transformación.
     - `EA`: Equipo auxiliar.
     - `EI`: Edificios e instalaciones.
     - `HT`: Hotel Telecom.
     - `EQ`: categoría observada en casos de tableros (hipótesis; revisar calidad por registros con observaciones de error).
     - `TG`: categoría observada asociada a buses en registros de baterías (hipótesis operativa).
   - Rama específica validada:
     - `...-SE-<sitio>-BL-<linea5>-<sistema>`
     - `<linea5>`: identificador de línea (5 caracteres).
     - `<sistema>`: `EEP` (equipo eléctrico primario), `ESP` (esquema de protección), `FOP` (sistema de fibra óptica), `OPL` (sistema OPLAT).
     - Nota de parsing: valores como `13_KV` o `T-020` corresponden a `<linea5>` (`Nivel 6`), no a niveles extra (cuidado con hacer split).
     - Subnivel observado para `EEP`:
       - `...-SE-<sitio>-BL-<linea5>-EEP-AP`: apartarrayos.
       - `...-SE-<sitio>-BL-<linea5>-EEP-CU`: cuchillas.
       - `...-SE-<sitio>-BL-<linea5>-EEP-DP`: dispositivo de potencial.
       - `...-SE-<sitio>-BL-<linea5>-EEP-IN`: interruptores.
       - `...-SE-<sitio>-BL-<linea5>-EEP-TC`: transformadores de corriente.
       - `...-SE-<sitio>-BL-<linea5>-EEP-TO`: trampas de onda.
       - `...-SE-<sitio>-BL-<linea5>-EEP-TP`: transformadores de potencial.
     - Subnivel observado para `ESP`:
       - `...-SE-<sitio>-BL-<linea5>-ESP-CA`: cable de control / fibra óptica.
       - `...-SE-<sitio>-BL-<linea5>-ESP-TA`: tablero de protección.
2. Para `Nivel 3 = LT`, el `Nivel 4` incluye:
   - `230`: línea de tensión de 230 kV (confirmado).
   - `M23`: líneas menores de 230 kV (confirmado).
   - `CDI`: `Circuito de Distribución` (hipótesis; no confirmado al 100%).
   - `400`: línea de tensión de 400 kV (confirmado).
   - `Nivel 5` observado por rama:
     - `M23`: `AE`, `AS`, `SU`.
     - `230`: `AE`, `AS`.
     - `CDI`: `AE`.
     - `400`: `AE`.
   - Significados confirmados en `Nivel 5` de LT:
     - `AS`: líneas aéreas-subterráneas.
     - `SU`: líneas subterráneas.
   - Rama validada:
     - `...-LT-<nivel_tension>-<nivel5>-<linea5>-<nivel7>[-<nivel8>]`
     - `<linea5>`: número/identificador de línea (5 caracteres; puede ser numérico o alfanumérico según fuente).
   - En este archivo, el `Nivel 7` observado para LT es `CFO` (cable de fibra óptica) en todas las combinaciones.
   - `Nivel 8` observado para LT-*-*-*-CFO:
     - `CE` (más frecuente).
     - `CF`.
   - Evidencia para `CDI` (muestras en `T-CAJAEMPALME`):
     - `BC-T013-LT-CDI-AE-C4195-CFO-CE` con `NOMBRE DE LA INSTALACIÓN = CICUITO4195` *(literal en fuente, posible error de captura).
     - `BC-T013-LT-CDI-AE-C4155-CFO-CE` con `NOMBRE DE LA INSTALACIÓN = ADSSHTPAZ-PAA`.
     - Ambos con `AREA = FOP` y enlaces ópticos asociados (`LPZ-HTPAZ`, `HTPAZ-PAA`), por lo que `CDI` se interpreta como `Circuito de Distribución` en una rama de enlaces/interconexión óptica (interpretación, pendiente confirmación).
     - Comportamiento estructural observado:
       - `CDI` aparece como `Nivel 4` únicamente dentro de `LT` (2 registros en este corte).
       - Después de `CDI`, el flujo continúa con `Nivel 5 = AE`, `Nivel 6 = C####`, `Nivel 7 = CFO`, `Nivel 8 = CE`.
       - Esto sugiere que `CDI` es una subrama de LT.
3. Para `Nivel 3 = TA`, el `Nivel 4` observado incluye:
   - `COM`: probable Comunicaciones (pendiente de validación formal).
   - `FOP`: probable Fibra Óptica (pendiente de validación formal).
   - `EDI`: rama para estructura de edificios (regla de nomenclatura funcional; no observada en el corte `TODOS_ZPM017` de BC).
   - `EQM`: Equipo de Metrología.
   - `EQP`: Equipo de Prueba y/o Medición.
   - `EQV`: Equipos Varios.
   - `ERO`: Equipo Retirado de Operación.
   - Nota: en el archivo `TODOS_ZPM017` también aparece `EQI`; pendiente validar si corresponde a una categoría distinta o a una variante de `EQP`.
   - Nomenclatura funcional reportada para rama de edificios:
     - `XX-NNNN-TA-EDI-YY-EDIOO`
     - `XX`: iniciales de GRT (`BC`, `NO`, `NT`, `NE`, `OC`, `CE`, `OR`, `PE`, `SE`).
     - `NNNN`: clave de centro de trabajo (centro gestor).
     - `TA`: rama de estructura técnico-administrativa.
     - `EDI`: estructura de edificios.
     - `YY`: clasificación de trabajos de ingeniería civil en edificios fuera de subestación (`AD` administrativo, `OP` operativo).
     - `EDIOO`: consecutivo de edificio (prefijo `EDI` + consecutivo `OO`).
4. Rama confirmada para equipos retirados/disponibles:
   - `Nivel 4 = ERO`: Equipo Retirado de Operación.
   - `Nivel 5 = DI`: Equipos Disponibles.
   - `Nivel 5 = NA`: No Activos.
   - Cuando `Nivel 4 = ERO` y `Nivel 5 = DI`, el `Nivel 6` clasifica familia técnica:
     - `EQCIV`: equipos de civil.
     - `EQCOM`: equipos de comunicaciones.
     - `EQCTG`: equipos de control de gestión.
     - `EQCTR`: equipos de control.
     - `EQLIN`: equipos de líneas.
     - `EQMET`: equipos de metrología.
     - `EQPRO`: equipos de protecciones.
     - `EQSUB`: equipos de subestaciones.

### Catálogo funcional confirmado de `Nivel 7` (Tipo de Equipo - 1er nivel)
1. `RAD`: Radiocomunicación.
2. `CUN`: Comunicaciones Unificadas.
3. `REI`: Red de Datos Operativa Smart Grid.
4. `IFR`: Infraestructura.
5. `OPL`: Red OPLAT.
6. `TEP`: Teleprotección.
7. `FOP`: Fibra Óptica.

### Catálogo funcional reportado de `Nivel 8` (Tipo de Equipo - 2do nivel)
1. `BPR`: Bus de Proceso.
2. `BBH`: Bus de Bahía.
3. `BES`: Bus de Estación.
4. `CEV`: Compensador Estático de VAR's.
5. `FACT`: Sistemas de Transmisión de AC Flexible.
6. `UTM`: Unidad Terminal Maestra.
7. Nota: estos códigos no aparecen en `TODOS_ZPM017`, pero se documentan como catálogo para futuras referencias.

## Modelado Base de Datos
En v1 solo se persiste el núcleo que ya soporta el esquema actual.

### Entidad: Ubicación técnica
Nombre tabla: `dm_ubicaciones_tecnicas`

Campos:
1. `codigo` (llave)
2. `nombre`
3. `nivel_1` (obligatorio)
4. `nivel_2` (opcional)
5. `nivel_3` (opcional)
6. `nivel_4` (opcional)
7. `nivel_5` (opcional)
8. `nivel_6` (opcional)
9. `nivel_7` (opcional)
10. `nivel_8` (opcional)
11. `activo`
12. `fuente` = `Manual | Importacion` *(ENUM)
13. `last_sync_at`

### Entidad: Equipo
Nombre tabla: `dm_equipos`

Campos:
1. `codigo` (llave)
2. `nombre`
3. `ubicacion_tecnica_id` (FK a `dm_ubicaciones_tecnicas.id`, opcional)
4. `area` (opcional)
5. `activo`
6. `fuente` = `Manual | Importacion` *(ENUM)
7. `last_sync_at`

## Llaves y Reglas de Upsert
1. Ubicación técnica:
   - `dm_ubicaciones_tecnicas.codigo` como llave única.
2. Equipo:
   - `dm_equipos.codigo` como llave única.
3. Relación equipo-ubicación:
   - La columna fuente `UBICACIÓN TÉCNICA` se resuelve por `dm_ubicaciones_tecnicas.codigo` y se persiste en `dm_equipos.ubicacion_tecnica_id`.

## Mapeo Inicial
### Fuente: LISTA DE EQUIPOS
Mapeo recomendado:
1. `Equipo` -> `dm_equipos.codigo`
2. `Denominación` -> `dm_equipos.nombre`
3. `Ubicación técnica` (columna fuente) -> `dm_ubicaciones_tecnicas.codigo` (resolver para relacionar equipo)
4. `Status sistema` (columna fuente) -> regla de `activo` (al cargar por defecto)
5. `Área de empresa` -> `dm_equipos.area` (opcional)

## Riesgos y Mitigaciones
1. Riesgo: variación de layout por hoja/archivo.
   - Mitigación: parser por encabezado, no por índice.
2. Riesgo: numéricos mezclados como texto.
   - Mitigación: normalizadores por tipo y validación.
