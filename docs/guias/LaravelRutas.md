# Guía de Laravel y Mapa de Rutas del Proyecto

## Objetivo
Este documento sirve como referencia de desarrollo para:
1. Entender los componentes de Laravel usados en el proyecto.
2. Ver su función y estado de uso real.
3. Tener el mapa completo de rutas (Laravel + Vue SPA).

## Arquitectura actual (resumen rápido)
1. Laravel expone rutas `web` para servir el shell de la SPA en raíz y manejar autenticación backend.
2. Vue Router maneja la navegación interna de la app bajo `/`.
3. Laravel expone API REST en `/api/v1/...`.
4. Sanctum protege endpoints autenticados con cookie/sesión (`auth:sanctum`).

Referencias:
- `routes/web.php`
- `routes/auth.php`
- `routes/api.php`
- `resources/js/router.js`
- `resources/views/spa.blade.php`

## Componentes clave de Laravel (general)
1. `Blade`: motor de plantillas para vistas renderizadas en servidor.
2. `Breeze`: kit base de autenticación (login, registro, reset, perfil).
3. `Sanctum`: autenticación para SPA/API con sesión por cookie o tokens.
4. `Eloquent`: ORM para trabajar tablas como modelos PHP.
5. `Migrations`: control de versión del esquema de base de datos.
6. `Seeders`: carga de datos iniciales o de prueba.
7. `Factories`: generación de datos sintéticos para pruebas/seed.
8. `Artisan`: CLI de Laravel (`migrate`, `seed`, `route:list`, etc.).
9. `Routing`: definición de endpoints web y API.
10. `Controllers`: lógica de cada endpoint.
11. `Middleware`: filtros por petición (`auth`, roles, CSRF, etc.).
12. `Form Requests`: validación de entrada encapsulada.
13. `Policies / Gates`: autorización por permisos y reglas de negocio.
14. `Service Providers`: registro y arranque de servicios del framework.
15. `Facades`: acceso estático a servicios (`DB`, `Cache`, `Auth`, etc.).
16. `Jobs / Queues`: tareas asíncronas en segundo plano.
17. `Events / Listeners`: arquitectura por eventos.
18. `Cache`: almacenamiento temporal para acelerar consultas.
19. `Notifications / Mail`: envío de notificaciones y correos.
20. `Vite`: compilación de assets frontend (Vue/JS/CSS).

## Uso real en este proyecto

### Activos en producción/desarrollo
1. `Blade`
- Se usa como shell SPA en `resources/views/spa.blade.php`.
- También existen vistas heredadas de auth/profile en `resources/views/auth/*` y `resources/views/profile/*`.

2. `Breeze`
- Instalado como base de auth (`composer.json`).
- Rutas auth en `routes/auth.php`.

3. `Sanctum`
- Dependencia activa en `composer.json`.
- Middleware stateful en `app/Http/Kernel.php` grupo `api`.
- Frontend solicita CSRF en `/sanctum/csrf-cookie` (`resources/js/bootstrap.js`, `resources/js/views/LoginView.vue`).

4. `Vue Router` (SPA)
- Definido en `resources/js/router.js`.
- Base actual: `createWebHistory()`.

5. `Routing Laravel`
- Web en `routes/web.php`.
- Auth en `routes/auth.php`.
- API en `routes/api.php`.

6. `Eloquent`
- Núcleo del dominio (`app/Models`).

7. `Migrations`
- Esquema versionado (`database/migrations`).

8. `Seeders`
- Datos base/demo (`database/seeders`).

9. `Form Requests`
- Validación estructurada (`app/Http/Requests`).

10. `API Resources`
- Serialización de respuestas (`app/Http/Resources`).

11. `Policies`
- `EntradaBitacoraPolicy` mapeada en `AuthServiceProvider`.
- Uso con `$this->authorize(...)` en controladores API.

12. `Spatie Permission`
- Roles/permisos activos con middleware `role`.

13. `Artisan Commands`
- Comando propio: `adjuntos:cleanup` (`app/Console/Commands/CleanupAdjuntosTemporales.php`).

14. `Vite`
- Build frontend mediante `@vite(...)` en vistas Blade.

15. `PHPUnit`
- Suite de pruebas disponible en `tests/`.

### Instalados/base con uso mínimo o no activo
1. `Jobs / Queues`
- No hay uso activo (`dispatch`, `Queue`, etc.).

2. `Broadcasting en tiempo real`
- Estructura base existe, cliente Echo comentado (`resources/js/bootstrap.js`).

3. `Notifications / Mail`
- Sin implementación activa.

4. `Tinker`
- Instalado para soporte/diagnóstico, no es flujo funcional de la app.

## Mapa completo de rutas Laravel

## Rutas `web` (`routes/web.php`)
1. `GET /` → vista `spa` (name: `spa`).
2. `GET /dashboard` → vista `spa` (middleware: `auth`, name: `dashboard`).
3. `GET /{any}` → vista `spa` (fallback SPA, excluye prefijos backend como `api`, `sanctum` y `storage`).

## Rutas `auth` (`routes/auth.php`)
1. `GET /login` (middleware: `guest`, name: `login`).
2. `POST /login` (middleware: `guest`).
3. `GET /confirm-password` (middleware: `auth`, name: `password.confirm`).
4. `POST /confirm-password` (middleware: `auth`).
5. `PUT /password` (middleware: `auth`, name: `password.update`).
6. `POST /logout` (middleware: `auth`, name: `logout`).

## Rutas `api` (`routes/api.php`)

### Públicas (sin auth)
1. `GET /api/v1/entradas`
2. `GET /api/v1/entradas/{entrada}`
3. `GET /api/v1/catalogos/criterios`
4. `GET /api/v1/catalogos/impactos`
5. `GET /api/v1/catalogos/pm/ordenes`
6. `GET /api/v1/catalogos/pm/actividades`
7. `GET /api/v1/catalogos/pm/matriz`
8. `GET /api/v1/sistemas-externos`
9. `GET /api/v1/configuracion/sistema`
10. `GET /api/v1/ubicaciones`
11. `GET /api/v1/ubicaciones/detalle-niveles`
12. `GET /api/v1/equipos`
13. `GET /api/v1/inventario/elementos`

### Autenticadas (`auth:sanctum`)
1. `GET /api/v1/me`

### Operador/Administrador (`auth:sanctum`, `role:operador|administrador`)
1. `GET /api/v1/dashboard/resumen`
2. `GET /api/v1/entradas/gestion`
3. `POST /api/v1/entradas`
4. `PUT /api/v1/entradas/{entrada}`
5. `DELETE /api/v1/entradas/{entrada}`
6. `POST /api/v1/entradas/{entrada}/publicar`
7. `POST /api/v1/entradas/{entrada}/despublicar`
8. `POST /api/v1/adjuntos/imagen`
9. `POST /api/v1/adjuntos/archivo`
10. `DELETE /api/v1/adjuntos/{adjunto}`
11. `POST /api/v1/referencias`
12. `GET /api/v1/configuracion/usuario`
13. `POST /api/v1/configuracion/usuario`
14. `PUT /api/v1/configuracion/usuario/password`
15. `PUT /api/v1/configuracion/usuario/{configuracion}`

### Administrador (`auth:sanctum`, `role:administrador`) prefijo `/api/v1/admin`
1. `GET|POST /catalogos/criterios`
2. `PUT|DELETE /catalogos/criterios/{criterio}`
3. `GET|POST /catalogos/impactos`
4. `PUT|DELETE /catalogos/impactos/{impacto}`
5. `GET|POST /pm/ordenes`
6. `PUT|DELETE /pm/ordenes/{orden}`
7. `GET|POST /pm/actividades`
8. `PUT|DELETE /pm/actividades/{actividad}`
9. `GET|POST /pm/matriz`
10. `PUT|DELETE /pm/matriz/{matriz}`
11. `GET|POST /sistemas-externos`
12. `PUT|DELETE /sistemas-externos/{sistema}`
13. `GET|POST /inventario/redes`
14. `PUT|DELETE /inventario/redes/{red}`
15. `GET|POST /inventario/dominios`
16. `PUT|DELETE /inventario/dominios/{dominio}`
17. `GET|POST /inventario/elementos`
18. `PUT|DELETE /inventario/elementos/{elemento}`
19. `GET|POST /inventario/import-reglas`
20. `PUT|DELETE /inventario/import-reglas/{regla}`
21. `GET /inventario/importaciones`
22. `GET /inventario/importaciones/{importacion}/errores`
23. `POST /inventario/importaciones/{importacion}/revertir`
24. `POST /inventario/importaciones/ejecutar`
25. `POST /depuracion/purgar-no-usados`
26. `GET|POST /dm/ubicaciones`
27. `PUT|DELETE /dm/ubicaciones/{ubicacion}`
28. `GET|POST /dm/equipos`
29. `PUT|DELETE /dm/equipos/{equipo}`
30. `GET|POST /dm/detalle-niveles`
31. `PUT|DELETE /dm/detalle-niveles/{detalleNivel}`
32. `POST /dm/importar-zpm017`
33. `PUT /configuracion/sistema/{configuracion}`
34. `POST /mantenimiento/adjuntos-temporales/cleanup`
35. `POST /mantenimiento/importaciones-archivos/cleanup`
36. `GET|POST /usuarios`
37. `PUT|DELETE /usuarios/{user}`
38. `GET /roles`

Nota: para validar el inventario exacto de rutas en el estado actual del código, usa `php artisan route:list`.

## Mapa completo de rutas Vue (SPA)
Base de la SPA: `/`.

Rutas internas definidas en `resources/js/router.js`:
1. `/` (redirect a `/timeline`)
2. `/timeline`
3. `/entradas/:id`
4. `/login`
5. `/dashboard`
6. `/pm-matriz`
7. `/catalogos-tecnicos`
8. `/sistemas-externos`
9. `/inventario-redes`
10. `/importaciones-reglas`
11. `/ubicaciones-equipos`
12. `/usuarios-roles`
13. `/configuracion`
14. `/entradas/nueva`
15. `/entradas/gestion`
16. `/entradas/:id/editar`

Rutas finales en navegador (ejemplos):
1. `/timeline`
2. `/dashboard`
3. `/importaciones-reglas`
4. `/entradas/123`

## Cómo conviven Laravel y Vue en este proyecto
1. Laravel responde `/` y `/{any}` (fallback web) con `spa.blade.php`.
2. Vue Router toma el control de navegación dentro de la SPA.
3. Vue consulta datos y acciones contra `/api/v1/...`.
4. Sanctum valida autenticación de endpoints protegidos.
5. Roles se aplican con middleware `role:*` en backend y guards en frontend.

## Guía rápida para desarrollar nuevas funcionalidades
1. Si es nueva pantalla: crear vista Vue y registrar ruta en `resources/js/router.js`.
2. Si requiere datos: crear endpoint en `routes/api.php` + controller, request y resource.
3. Si requiere permisos: agregar middleware `auth:sanctum` + `role:*` y/o `Policy`.
4. Si requiere persistencia: crear migración + modelo Eloquent + pruebas.
5. Si requiere datos base: agregar seeder.
6. Si requiere navegación SPA: mantener rutas bajo `/*` (raíz) y dejar backend en `/api/*` + `/sanctum/*`.

## Referencias técnicas del proyecto
1. `composer.json`
2. `app/Http/Kernel.php`
3. `app/Providers/AuthServiceProvider.php`
4. `app/Policies/EntradaBitacoraPolicy.php`
5. `resources/js/bootstrap.js`
6. `resources/js/router.js`
7. `resources/views/spa.blade.php`
8. `routes/web.php`
9. `routes/auth.php`
10. `routes/api.php`
