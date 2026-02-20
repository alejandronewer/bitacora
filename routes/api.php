<?php

use App\Http\Controllers\Api\AdjuntoController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\ConfiguracionUsuarioController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EntradaController;
use App\Http\Controllers\Api\EquipoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\ReferenciaExternaController;
use App\Http\Controllers\Api\SistemaExternoController;
use App\Http\Controllers\Api\UbicacionDetalleNivelController;
use App\Http\Controllers\Api\UbicacionTecnicaController;
use App\Http\Controllers\Api\Admin\CatEntradaCriterioController;
use App\Http\Controllers\Api\Admin\CatEntradaImpactoController;
use App\Http\Controllers\Api\Admin\ConfiguracionSistemaController;
use App\Http\Controllers\Api\Admin\DepuracionDatosAdminController;
use App\Http\Controllers\Api\Admin\DmEquipoAdminController;
use App\Http\Controllers\Api\Admin\DmSapImportController;
use App\Http\Controllers\Api\Admin\DmUbicacionDetalleNivelAdminController;
use App\Http\Controllers\Api\Admin\DmUbicacionTecnicaAdminController;
use App\Http\Controllers\Api\Admin\InvDominioAdminController;
use App\Http\Controllers\Api\Admin\InvElementoRedAdminController;
use App\Http\Controllers\Api\Admin\InvImportReglaAdminController;
use App\Http\Controllers\Api\Admin\InvRedAdminController;
use App\Http\Controllers\Api\Admin\MantenimientoAdminController;
use App\Http\Controllers\Api\Admin\PmClaseActividadController;
use App\Http\Controllers\Api\Admin\PmClaseOrdenController;
use App\Http\Controllers\Api\Admin\PmMatrizOrdenActividadController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\SistemaExternoAdminController;
use App\Http\Controllers\Api\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('entradas', [EntradaController::class, 'index']);
    Route::get('entradas/{entrada}', [EntradaController::class, 'show'])->whereNumber('entrada');

    Route::get('catalogos/criterios', [CatalogoController::class, 'criterios']);
    Route::get('catalogos/impactos', [CatalogoController::class, 'impactos']);
    Route::get('catalogos/pm/ordenes', [CatalogoController::class, 'pmOrdenes']);
    Route::get('catalogos/pm/actividades', [CatalogoController::class, 'pmActividades']);
    Route::get('catalogos/pm/matriz', [CatalogoController::class, 'pmMatriz']);

    Route::get('sistemas-externos', [SistemaExternoController::class, 'index']);
    Route::get('configuracion/sistema', [ConfiguracionSistemaController::class, 'index']);
    Route::get('ubicaciones', [UbicacionTecnicaController::class, 'index']);
    Route::get('ubicaciones/detalle-niveles', [UbicacionDetalleNivelController::class, 'index']);
    Route::get('equipos', [EquipoController::class, 'index']);
    Route::get('inventario/elementos', [InventarioController::class, 'elementos']);

    Route::middleware(['auth:sanctum'])->get('me', [MeController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:operador|administrador'])->group(function () {
        Route::get('dashboard/resumen', [DashboardController::class, 'resumen']);
        Route::get('entradas/gestion', [EntradaController::class, 'gestion']);
        Route::post('entradas', [EntradaController::class, 'store']);
        Route::put('entradas/{entrada}', [EntradaController::class, 'update']);
        Route::delete('entradas/{entrada}', [EntradaController::class, 'destroy']);
        Route::post('entradas/{entrada}/publicar', [EntradaController::class, 'publicar']);
        Route::post('entradas/{entrada}/despublicar', [EntradaController::class, 'despublicar']);

        Route::post('adjuntos/imagen', [AdjuntoController::class, 'storeImagen']);
        Route::post('adjuntos/archivo', [AdjuntoController::class, 'storeArchivo']);
        Route::delete('adjuntos/{adjunto}', [AdjuntoController::class, 'destroy']);

        Route::post('referencias', [ReferenciaExternaController::class, 'store']);

        Route::get('configuracion/usuario', [ConfiguracionUsuarioController::class, 'index']);
        Route::post('configuracion/usuario', [ConfiguracionUsuarioController::class, 'store']);
        Route::put('configuracion/usuario/password', [ConfiguracionUsuarioController::class, 'updatePassword']);
        Route::put('configuracion/usuario/{configuracion}', [ConfiguracionUsuarioController::class, 'update']);
    });

    Route::middleware(['auth:sanctum', 'role:administrador'])->prefix('admin')->group(function () {
        Route::get('catalogos/criterios', [CatEntradaCriterioController::class, 'index']);
        Route::post('catalogos/criterios', [CatEntradaCriterioController::class, 'store']);
        Route::put('catalogos/criterios/{criterio}', [CatEntradaCriterioController::class, 'update']);
        Route::delete('catalogos/criterios/{criterio}', [CatEntradaCriterioController::class, 'destroy']);

        Route::get('catalogos/impactos', [CatEntradaImpactoController::class, 'index']);
        Route::post('catalogos/impactos', [CatEntradaImpactoController::class, 'store']);
        Route::put('catalogos/impactos/{impacto}', [CatEntradaImpactoController::class, 'update']);
        Route::delete('catalogos/impactos/{impacto}', [CatEntradaImpactoController::class, 'destroy']);

        Route::get('pm/ordenes', [PmClaseOrdenController::class, 'index']);
        Route::post('pm/ordenes', [PmClaseOrdenController::class, 'store']);
        Route::put('pm/ordenes/{orden}', [PmClaseOrdenController::class, 'update']);
        Route::delete('pm/ordenes/{orden}', [PmClaseOrdenController::class, 'destroy']);

        Route::get('pm/actividades', [PmClaseActividadController::class, 'index']);
        Route::post('pm/actividades', [PmClaseActividadController::class, 'store']);
        Route::put('pm/actividades/{actividad}', [PmClaseActividadController::class, 'update']);
        Route::delete('pm/actividades/{actividad}', [PmClaseActividadController::class, 'destroy']);

        Route::get('pm/matriz', [PmMatrizOrdenActividadController::class, 'index']);
        Route::post('pm/matriz', [PmMatrizOrdenActividadController::class, 'store']);
        Route::put('pm/matriz/{matriz}', [PmMatrizOrdenActividadController::class, 'update']);
        Route::delete('pm/matriz/{matriz}', [PmMatrizOrdenActividadController::class, 'destroy']);

        Route::get('sistemas-externos', [SistemaExternoAdminController::class, 'index']);
        Route::post('sistemas-externos', [SistemaExternoAdminController::class, 'store']);
        Route::put('sistemas-externos/{sistema}', [SistemaExternoAdminController::class, 'update']);
        Route::delete('sistemas-externos/{sistema}', [SistemaExternoAdminController::class, 'destroy']);

        Route::get('inventario/redes', [InvRedAdminController::class, 'index']);
        Route::post('inventario/redes', [InvRedAdminController::class, 'store']);
        Route::put('inventario/redes/{red}', [InvRedAdminController::class, 'update']);
        Route::delete('inventario/redes/{red}', [InvRedAdminController::class, 'destroy']);

        Route::get('inventario/dominios', [InvDominioAdminController::class, 'index']);
        Route::post('inventario/dominios', [InvDominioAdminController::class, 'store']);
        Route::put('inventario/dominios/{dominio}', [InvDominioAdminController::class, 'update']);
        Route::delete('inventario/dominios/{dominio}', [InvDominioAdminController::class, 'destroy']);

        Route::get('inventario/elementos', [InvElementoRedAdminController::class, 'index']);
        Route::post('inventario/elementos', [InvElementoRedAdminController::class, 'store']);
        Route::put('inventario/elementos/{elemento}', [InvElementoRedAdminController::class, 'update']);
        Route::delete('inventario/elementos/{elemento}', [InvElementoRedAdminController::class, 'destroy']);

        Route::get('inventario/import-reglas', [InvImportReglaAdminController::class, 'index']);
        Route::post('inventario/import-reglas', [InvImportReglaAdminController::class, 'store']);
        Route::put('inventario/import-reglas/{regla}', [InvImportReglaAdminController::class, 'update']);
        Route::delete('inventario/import-reglas/{regla}', [InvImportReglaAdminController::class, 'destroy']);

        Route::get('inventario/importaciones', [InvImportReglaAdminController::class, 'importaciones']);
        Route::get('inventario/importaciones/{importacion}/errores', [InvImportReglaAdminController::class, 'errores']);
        Route::post('inventario/importaciones/{importacion}/revertir', [InvImportReglaAdminController::class, 'revertir']);
        Route::post('inventario/importaciones/ejecutar', [InvImportReglaAdminController::class, 'ejecutar']);
        Route::post('depuracion/purgar-no-usados', [DepuracionDatosAdminController::class, 'purgarNoUsados']);
        Route::post('mantenimiento/adjuntos-temporales/cleanup', [MantenimientoAdminController::class, 'cleanupAdjuntosTemporales']);
        Route::post('mantenimiento/importaciones-archivos/cleanup', [MantenimientoAdminController::class, 'cleanupImportFiles']);

        Route::get('dm/ubicaciones', [DmUbicacionTecnicaAdminController::class, 'index']);
        Route::post('dm/ubicaciones', [DmUbicacionTecnicaAdminController::class, 'store']);
        Route::put('dm/ubicaciones/{ubicacion}', [DmUbicacionTecnicaAdminController::class, 'update']);
        Route::delete('dm/ubicaciones/{ubicacion}', [DmUbicacionTecnicaAdminController::class, 'destroy']);

        Route::get('dm/equipos', [DmEquipoAdminController::class, 'index']);
        Route::post('dm/equipos', [DmEquipoAdminController::class, 'store']);
        Route::put('dm/equipos/{equipo}', [DmEquipoAdminController::class, 'update']);
        Route::delete('dm/equipos/{equipo}', [DmEquipoAdminController::class, 'destroy']);
        Route::get('dm/detalle-niveles', [DmUbicacionDetalleNivelAdminController::class, 'index']);
        Route::post('dm/detalle-niveles', [DmUbicacionDetalleNivelAdminController::class, 'store']);
        Route::put('dm/detalle-niveles/{detalleNivel}', [DmUbicacionDetalleNivelAdminController::class, 'update']);
        Route::delete('dm/detalle-niveles/{detalleNivel}', [DmUbicacionDetalleNivelAdminController::class, 'destroy']);
        Route::post('dm/importar-zpm017', [DmSapImportController::class, 'importarZpm017']);

        Route::put('configuracion/sistema/{configuracion}', [ConfiguracionSistemaController::class, 'update']);

        Route::get('usuarios', [UserController::class, 'index']);
        Route::post('usuarios', [UserController::class, 'store']);
        Route::put('usuarios/{user}', [UserController::class, 'update']);
        Route::delete('usuarios/{user}', [UserController::class, 'destroy']);

        Route::get('roles', [RoleController::class, 'index']);
    });
});
