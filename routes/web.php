<?php

use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Requerimientos\AdjuntoController;
use App\Http\Controllers\Requerimientos\CalendarioController;
use App\Http\Controllers\Requerimientos\CasoUsoController;
use App\Http\Controllers\Requerimientos\FlujoController;
use App\Http\Controllers\Requerimientos\KanbanController;
use App\Http\Controllers\Requerimientos\RequerimientoController;
use App\Services\RequerimientoFlujoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('usuarios/data', [UserController::class, 'data'])->name('usuarios.data');
        Route::get('usuarios/pdf', [UserController::class, 'pdf'])->name('usuarios.pdf');
        Route::resource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'user'])
            ->except('show');

        Route::post('roles/data', [RoleController::class, 'data'])->name('roles.data');
        Route::resource('roles', RoleController::class)->except('show');

        Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::post('bitacora/data', [BitacoraController::class, 'data'])->name('bitacora.data');
        Route::post('bitacora/sesiones/data', [BitacoraController::class, 'sessionsData'])->name('bitacora.sesiones.data');
        Route::post('bitacora/sesiones/{sesion}/invalidar', [BitacoraController::class, 'invalidateSession'])
            ->name('bitacora.sesiones.invalidar')
            ->where('sesion', '[a-zA-Z0-9]+');
        Route::get('bitacora/sesiones/{sesion}', [BitacoraController::class, 'showSesion'])
            ->name('bitacora.sesiones.show')
            ->where('sesion', '[a-zA-Z0-9]+');
        Route::get('bitacora/pdf', [BitacoraController::class, 'pdf'])->name('bitacora.pdf');
        Route::get('bitacora/{bitacora}', [BitacoraController::class, 'show'])->name('bitacora.show');

        Route::post('permisos/data', [PermissionController::class, 'data'])->name('permisos.data');
        Route::resource('permisos', PermissionController::class)
            ->parameters(['permisos' => 'permission'])
            ->except('show');
    });

    Route::prefix('requerimientos')->name('requerimientos.')->group(function () {
        Route::post('data', [RequerimientoController::class, 'data'])->name('data');
        Route::get('bandeja', [RequerimientoController::class, 'bandeja'])->name('bandeja');
        Route::get('kanban', [KanbanController::class, 'index'])->name('kanban');
        Route::get('calendario', [CalendarioController::class, 'index'])->name('calendario');
        Route::get('calendario/eventos', [CalendarioController::class, 'eventos'])->name('calendario.eventos');

        Route::get('{requerimiento}/pdf', [RequerimientoController::class, 'pdf'])->name('pdf');
        Route::post('{requerimiento}/flujo/{accion}', FlujoController::class)
            ->name('flujo')
            ->whereIn('accion', array_keys(RequerimientoFlujoService::ACCIONES));

        Route::scopeBindings()->group(function () {
            Route::post('{requerimiento}/adjuntos', [AdjuntoController::class, 'store'])->name('adjuntos.store');
            Route::get('{requerimiento}/adjuntos/{adjunto}', [AdjuntoController::class, 'show'])->name('adjuntos.show');
            Route::get('{requerimiento}/adjuntos/{adjunto}/descargar', [AdjuntoController::class, 'descargar'])->name('adjuntos.descargar');
            Route::delete('{requerimiento}/adjuntos/{adjunto}', [AdjuntoController::class, 'destroy'])->name('adjuntos.destroy');
        });
    });

    Route::resource('requerimientos', RequerimientoController::class);
    Route::resource('requerimientos.casos-uso', CasoUsoController::class)
        ->parameters(['casos-uso' => 'casoUso'])
        ->except(['index', 'show']);
});
