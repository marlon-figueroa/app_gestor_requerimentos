<?php

use App\Http\Controllers\Api\ProyectoApiController;
use App\Http\Controllers\Api\RequerimientoApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1')->name('api.')->group(function () {
    Route::get('proyectos', [ProyectoApiController::class, 'index'])->name('proyectos.index');
    Route::get('proyectos/{proyecto}', [ProyectoApiController::class, 'show'])->name('proyectos.show');
    Route::get('requerimientos', [RequerimientoApiController::class, 'index'])->name('requerimientos.index');
    Route::get('requerimientos/{requerimiento}', [RequerimientoApiController::class, 'show'])->name('requerimientos.show');
});
