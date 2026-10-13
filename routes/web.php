<?php

use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
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
});
