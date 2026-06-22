<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\SucursalController;

Route::prefix('configuracion')->group(function () {
    // Empresas
    Route::apiResource('empresas', EmpresaController::class);
    Route::put('empresas/{id}/status', [EmpresaController::class, 'switchStatus']);
    Route::get('paises', [EmpresaController::class, 'paises']);

    // Sucursales
    // Sucursales
    Route::get('sucursales/empresas', [SucursalController::class, 'empresas'])->middleware('permission:sucursales.view');
    Route::apiResource('sucursales', SucursalController::class)->middleware('permission:sucursales.view');
    Route::put('sucursales/{id}/status', [SucursalController::class, 'switchStatus'])->middleware('permission:sucursales.edit');
});
