<?php

use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\Proyectos\AdjuntoController as ProyectoAdjuntoController;
use App\Http\Controllers\Proyectos\EvaluacionPfController;
use App\Http\Controllers\Proyectos\FlujoController as ProyectoFlujoController;
use App\Http\Controllers\Proyectos\ProyectoController;
use App\Http\Controllers\Requerimientos\AdjuntoController;
use App\Http\Controllers\Requerimientos\ComentarioController;
use App\Http\Controllers\Requerimientos\CalendarioController;
use App\Http\Controllers\Requerimientos\CasoUsoController;
use App\Http\Controllers\Requerimientos\FlujoController;
use App\Http\Controllers\Requerimientos\KanbanController;
use App\Http\Controllers\Requerimientos\RequerimientoController;
use App\Services\ProyectoFlujoService;
use App\Services\RequerimientoFlujoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Controllers\ImpersonateController;

Route::redirect('/', '/home');

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('perfil', [PerfilController::class, 'show'])->name('perfil.show');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('perfil/contrasena', [PerfilController::class, 'updatePassword'])->name('perfil.password');

    Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('notificaciones/leidas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.leidas');

    // POST para que el cambio de sesión exija el token CSRF.
    Route::post('impersonate/take/{id}', [ImpersonateController::class, 'take'])
        ->name('impersonate')
        ->whereNumber('id');
    Route::post('impersonate/leave', [ImpersonateController::class, 'leave'])
        ->name('impersonate.leave');

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

    Route::prefix('proyectos')->name('proyectos.')->group(function () {
        Route::get('mis-proyectos', [ProyectoController::class, 'misProyectos'])->name('mis-proyectos');
        Route::post('data', [ProyectoController::class, 'data'])->name('data');
        Route::get('{proyecto}/pdf', [ProyectoController::class, 'pdf'])->name('pdf');
        Route::post('{proyecto}/flujo/{accion}', ProyectoFlujoController::class)
            ->name('flujo')
            ->whereIn('accion', array_keys(ProyectoFlujoService::ACCIONES));

        Route::scopeBindings()->group(function () {
            Route::post('{proyecto}/adjuntos', [ProyectoAdjuntoController::class, 'store'])->name('adjuntos.store');
            Route::get('{proyecto}/adjuntos/{adjunto}', [ProyectoAdjuntoController::class, 'show'])->name('adjuntos.show');
            Route::get('{proyecto}/adjuntos/{adjunto}/descargar', [ProyectoAdjuntoController::class, 'descargar'])->name('adjuntos.descargar');
            Route::delete('{proyecto}/adjuntos/{adjunto}', [ProyectoAdjuntoController::class, 'destroy'])->name('adjuntos.destroy');
        });

        Route::get('{proyecto}/evaluaciones-pf', [EvaluacionPfController::class, 'index'])->name('evaluaciones-pf.index');
        Route::get('{proyecto}/evaluaciones-pf/create', [EvaluacionPfController::class, 'create'])->name('evaluaciones-pf.create');
        Route::post('{proyecto}/evaluaciones-pf', [EvaluacionPfController::class, 'store'])->name('evaluaciones-pf.store');
        Route::get('{proyecto}/evaluaciones-pf/{evaluacion_pf}', [EvaluacionPfController::class, 'show'])->name('evaluaciones-pf.show');
        Route::get('{proyecto}/evaluaciones-pf/{evaluacion_pf}/edit', [EvaluacionPfController::class, 'edit'])->name('evaluaciones-pf.edit');
        Route::put('{proyecto}/evaluaciones-pf/{evaluacion_pf}', [EvaluacionPfController::class, 'update'])->name('evaluaciones-pf.update');
        Route::delete('{proyecto}/evaluaciones-pf/{evaluacion_pf}', [EvaluacionPfController::class, 'destroy'])->name('evaluaciones-pf.destroy');
        Route::get('{proyecto}/evaluaciones-pf/{evaluacion_pf}/pdf', [EvaluacionPfController::class, 'pdf'])->name('evaluaciones-pf.pdf');
        Route::post('{proyecto}/evaluaciones-pf/{evaluacion_pf}/duplicate', [EvaluacionPfController::class, 'duplicate'])->name('evaluaciones-pf.duplicate');
        Route::post('{proyecto}/evaluaciones-pf/{evaluacion_pf}/baseline', [EvaluacionPfController::class, 'baseline'])->name('evaluaciones-pf.baseline');
    });

    Route::resource('proyectos', ProyectoController::class)->except('destroy');

    Route::prefix('requerimientos')->name('requerimientos.')->group(function () {
        Route::post('data', [RequerimientoController::class, 'data'])->name('data');
        Route::get('bandeja', [RequerimientoController::class, 'bandeja'])->name('bandeja');
        Route::get('kanban', [KanbanController::class, 'index'])->name('kanban');
        Route::get('calendario', [CalendarioController::class, 'index'])->name('calendario');
        Route::get('calendario/eventos', [CalendarioController::class, 'eventos'])->name('calendario.eventos');
        Route::get('export.csv', [RequerimientoController::class, 'exportCsv'])->name('export.csv');

        Route::post('{requerimiento}/comentarios', [ComentarioController::class, 'store'])->name('comentarios.store');

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
