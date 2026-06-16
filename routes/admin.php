<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| All admin panel routes are defined here, protected by the 'auth' and
| 'verified' middleware. Module routes are loaded from routes/modules/.
|
*/

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function () {
    // Load module routes
    require __DIR__.'/modules/dashboard.php';
    require __DIR__.'/modules/catalogo.php';
    require __DIR__.'/modules/ventas.php';
    require __DIR__.'/modules/empresas.php';
    require __DIR__.'/modules/grupos.php';
    require __DIR__.'/modules/integraciones.php';
    require __DIR__.'/modules/inventario.php';
    require __DIR__.'/modules/pagos.php';
    require __DIR__.'/modules/monitoreo.php';
    require __DIR__.'/modules/paises.php';
    require __DIR__.'/modules/roles.php';
    require __DIR__.'/modules/sucursales.php';
    require __DIR__.'/modules/users.php';
});
