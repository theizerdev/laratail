<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo: Seguridad
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas para gestionar Usuarios, Roles y Permisos.
|
*/

use App\Http\Controllers\Seguridad\UserController;
use App\Http\Controllers\Seguridad\RoleController;
use App\Http\Controllers\Seguridad\PermissionController;
use App\Http\Controllers\Seguridad\PaisController;

Route::apiResource('usuarios', UserController::class)->middleware('permission:users.view');
Route::apiResource('roles', RoleController::class)->middleware('permission:roles.view');
Route::apiResource('paises', PaisController::class)->middleware('permission:countries.view');
Route::get('permisos', [PermissionController::class, 'index'])->middleware('permission:permissions.view');
