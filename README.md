# Bitácora Operativa

Aplicación web para registrar, publicar y consultar eventos operativos, con integración de:
- Inventario NMS/EMS
- Ubicaciones técnicas y equipos (SAP-like)
- Referencias externas (tickets/folios)
- Clasificación operativa (criterio/impacto) y matriz PM

## Stack tecnológico
- Backend: Laravel 10 (API REST)
- Frontend: Vue 3 + Vite (SPA)
- Base de datos: MySQL 8
- Autenticación: Laravel Sanctum (sesión + CSRF)
- Autorización: Spatie Roles/Permissions

## Requisitos
- PHP 8.1+
- Composer
- Node.js 20+
- MySQL 8
- Extensión PHP GD (procesamiento de imágenes)
- Otras extensiones `mysqlnd, opcache, pdo, xml, calendar, ctype, curl, dom, exif, ffi, fileinfo, ftp, gd, gettext, iconv, mbstring, mysqli, pdo_mysql, phar, posix, readline, shmop, simplexml, sockets, sysvmsg, sysvsem, sysvshm, tokenizer, xmlreader, xmlwriter, xsl, zip`

## Arranque rápido (desarrollo)
1. Copiar variables de entorno:
   - `cp .env.example .env`
2. Configurar base de datos en `.env`.
3. Instalar dependencias:
   - `composer install`
   - `npm install`
4. Generar key:
   - `php artisan key:generate`
5. Crear esquema y datos base:
   - `php artisan migrate --seed`
6. Publicar enlace de storage:
   - `php artisan storage:link`
7. Levantar servicios:
   - Backend: `php artisan serve --host=0.0.0.0 --port=8000`
   - Frontend: `npm run dev -- --host 127.0.0.1 --port 5173`

Opcional (Podman + Makefile):
- `make up` (MySQL)
- `make serve` (Laravel)
- `make dev` (Vite)

## Usuarios iniciales (seeder)
Creado por `ConfiguracionSistemaSeeder`:
- Administrador: `admin@bitacora.test`
- Operador: `operador@bitacora.test`
- Contraseña inicial: `12345678`

Recomendación: cambiar contraseña en el primer acceso.

## Arquitectura
- Laravel sirve la SPA en raíz (`/`) y expone API en `/api/v1/*`.
- Vue Router trabaja en rutas de raíz (sin prefijo `/app`).

Rutas web clave:
- `/` → SPA
- `/dashboard` → SPA (requiere sesión backend)
- `/{any}` fallback SPA (excepto `api|sanctum|storage`)

## Rutas SPA principales
- `/timeline`
- `/login`
- `/dashboard`
- `/entradas/nueva`
- `/entradas/:id`
- `/entradas/:id/editar`
- `/entradas/gestion`
- `/inventario-redes`
- `/importaciones-reglas`
- `/ubicaciones-equipos`
- `/catalogos-tecnicos`
- `/sistemas-externos`
- `/pm-matriz`
- `/usuarios-roles`
- `/configuracion`

## API (resumen)
### Públicas
- Entradas (listado/detalle)
- Catálogos activos (criterios, impactos, PM)
- Sistemas externos activos
- Configuración pública
- Inventario de elementos activos
- Ubicaciones técnicas y equipos activos
- Catálogo de detalle de niveles de ubicación

### Protegidas (operador|administrador)
- CRUD de entradas
- Publicar/despublicar entradas
- Adjuntos (imagen/archivo)
- Alta de referencias externas
- Configuración de usuario (incluye cambio de contraseña)

### Administrador
- CRUD catálogos operativos
- CRUD PM (órdenes, actividades, matriz)
- CRUD sistemas externos
- CRUD inventario (redes, dominios, elementos)
- CRUD reglas de importación y ejecución/reversión
- CRUD ubicaciones/equipos + detalle de niveles
- Importación SAP-like (`importar-zpm017`)
- Limpieza de datos no usados
- Mantenimiento de adjuntos temporales
- Gestión de configuración de sistema
- Gestión de usuarios/roles

Referencia completa de rutas:
- `docs/guias/LaravelRutas.md`

## Módulos de negocio
- Bitácora de entradas (borrador/publicación)
- Inventario NMS/EMS con detalle por tipo (`nodo`, `enlace`, `servicio`, `tunel`)
- Reglas de importación CSV y bitácora de ejecuciones/errores
- Ubicaciones técnicas y equipos SAP-like
- Catálogos operativos (criterio/impacto)
- Matriz PM (clase de orden / clase de actividad)
- Referencias externas con validación por regex

## Seeders activos
Ejecutados por `DatabaseSeeder`:
- `ConfiguracionSistemaSeeder`
- `CatalogoOperativoSeeder`
- `PmLikeSeeder`
- `SistemasExternosSeeder`
- `InventarioRedesDominiosSeeder`
- `ImportRuleSeeder`

## Migraciones consolidadas
Orden actual (prefijo `001..012`):
1. `001_create_usuarios_table.php`
2. `002_create_spatie_permission_tables.php`
3. `003_create_configuracion_tables.php`
4. `004_create_dm_ubicaciones_y_equipos_tables.php`
5. `005_create_catalogos_operativos_tables.php`
6. `006_create_pm_tables.php`
7. `007_create_entradas_bitacora_tables.php`
8. `008_create_adjuntos_table.php`
9. `009_create_inventario_catalogos_tables.php`
10. `010_create_inventario_importaciones_tables.php`
11. `011_create_inventario_elementos_tables.php`
12. `012_create_sistemas_externos_tables.php`

## Rate limiting (429)
Configurable por entorno en `.env`:
- `RATE_LIMIT_API_ENABLED`
- `RATE_LIMIT_API_MAX_ATTEMPTS`
- `RATE_LIMIT_API_DECAY_MINUTES`
- `RATE_LIMIT_LOGIN_ENABLED`
- `RATE_LIMIT_LOGIN_MAX_ATTEMPTS`
- `RATE_LIMIT_LOGIN_DECAY_SECONDS`

Después de cambiar estos valores ejecutar:
- `php artisan optimize:clear`

## Adjuntos y limpieza
- Imágenes y archivos se guardan en `storage/app/public/adjuntos/*`.
- Las imágenes se normalizan a PNG.
- Existen adjuntos temporales mientras no se asocian a una entrada.
- Los archivos fuente de importación se guardan en `storage/app/imports/*`.

Limpieza (CLI):
- Simulación: `php artisan adjuntos:cleanup --hours=24 --dry-run`
- Ejecución: `php artisan adjuntos:cleanup --hours=24`
- Simulación importaciones: `php artisan imports:cleanup --hours=24 --dry-run`
- Ejecución importaciones: `php artisan imports:cleanup --hours=24`

La limpieza considera:
- Temporales en BD (`entrada_id = NULL`)
- Archivos huérfanos en disco sin registro en BD
- Archivos de importación antiguos referenciados en `inv_importaciones_redes.fuente`
- Archivos huérfanos en `storage/app/imports` sin referencia en BD

También disponible en UI:
- `Configuración > Configuración del sistema > Mantenimiento` (solo administrador)

## Comandos útiles
- `php artisan migrate:fresh --seed`
- `php artisan optimize:clear`
- `npm run dev -- --host 127.0.0.1 --port 5173`
- `npm run build`

## Producción (pre-checklist y despliegue)
### Pre-checklist
- Servidor con PHP 8.1+, extensiones requeridas (incluyendo `gd`), Composer y MySQL 8.
- Servidor web (Nginx/Apache) apuntando a `public/`.
- Variables de entorno productivas en `.env`:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://tu-dominio`
  - conexión DB real (`DB_*`)
  - correo/sesión/cache según tu infraestructura
- Rate limit habilitado en producción:
  - `RATE_LIMIT_API_ENABLED=true`
  - `RATE_LIMIT_LOGIN_ENABLED=true`
- Credenciales iniciales cambiadas (usuarios seed por defecto).

### Primer despliegue
1. Instalar dependencias:
   - `composer install --no-dev --optimize-autoloader`
   - `npm ci`
2. Compilar frontend:
   - `npm run build`
3. Publicar storage:
   - `php artisan storage:link`
4. Crear esquema y datos base:
   - `php artisan migrate --force`
   - `php artisan db:seed --force`
5. Optimizar caches:
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`

### Actualización (release normal)
1. Actualizar código.
2. Instalar dependencias (si cambiaron):
   - `composer install --no-dev --optimize-autoloader`
   - `npm ci`
3. Recompilar assets:
   - `npm run build`
4. Ejecutar migraciones nuevas:
   - `php artisan migrate --force`
5. Refrescar caches:
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`

### Mantenimiento programado recomendado
- Programar limpieza de adjuntos temporales y huérfanos (cron):
  - `php artisan adjuntos:cleanup --hours=24`
- Programar limpieza de archivos de importación antiguos:
  - `php artisan imports:cleanup --hours=24`
- Si usarás scheduler de Laravel para más tareas:
  - cron cada x intervalo de tiempo con `php artisan schedule:run`

## Documentación del proyecto
- Rutas Laravel/API: `docs/guias/LaravelRutas.md`
- Datos maestros SAP-like: `docs/guias/DatosMaestrosSAP.md`
- Modelo de datos DBML: `docs/diagramas/bitacora.dbml`
- Diagrama PlantUML: `docs/diagramas/bitacora.puml`

## Fixes recientes (pre-release v0.1.0)
- Corrección de captura de fecha/hora en creación/edición: se reemplazó `datetime-local` por pares `date` + `time` para mejorar compatibilidad entre navegadores.
- Corrección en generación de IDs temporales de referencias externas: ahora usa `crypto.randomUUID()` cuando está disponible y fallback local en HTTP/contextos sin `window.crypto`.
- Corrección en desasociación de inventario NMS/EMS: al guardar edición se envía siempre `inventario_elementos`, permitiendo dejar correctamente 0 elementos asociados.
- Corrección de redirección involuntaria a login en Timeline: se evitó redirección por llamadas opcionales de configuración de usuario sin sesión.
- Aplicación efectiva de `bitacora_publica` para Timeline y detalle de entrada (frontend y backend): cuando está desactivada exige sesión; cuando está activa permite acceso público.
- Corrección de render HTML en vista detalle: se agregaron estilos de contenido enriquecido para listas ordenadas/no ordenadas, párrafos, enlaces, blockquotes e imágenes.
- Corrección de sincronización de imágenes en editor WYSIWYG: al eliminar una imagen del contenido, ahora se desasocia de la entrada (y se elimina temporal/persistida según corresponda).
- Mejora en vista detalle de adjuntos de imagen: miniaturas clickeables con vista ampliada (modal).
- Corrección de guardados consecutivos tras eliminar adjuntos: se limpian colas `adjuntos_eliminar` y `referencias_eliminar` después de guardar para evitar errores de validación por IDs ya eliminados.
