<?php

use App\Livewire\Admin\Reportes\Dashboard;
use App\Livewire\Admin\Reportes\Ventas;
use App\Livewire\Admin\Reportes\Pagos;
use App\Livewire\Admin\Reportes\Inventario;
use Illuminate\Support\Facades\Route;

Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/ventas', Ventas::class)->name('ventas');
    Route::get('/pagos', Pagos::class)->name('pagos');
    Route::get('/inventario', Inventario::class)->name('inventario');
});
