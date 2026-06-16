<?php

use App\Livewire\Admin\Pagos\Pagos;
use App\Livewire\Admin\Pagos\Facturas;
use App\Livewire\Admin\Pagos\NotasCredito;
use App\Livewire\Admin\Pagos\Caja;
use App\Livewire\Admin\Pagos\Conciliacion;
use Illuminate\Support\Facades\Route;

// Pagos
Route::get('/pagos', Pagos\Index::class)
    ->name('pagos');

// Facturas
Route::get('/facturas', Facturas\Index::class)
    ->name('facturas');
Route::get('/facturas/create', Facturas\Create::class)
    ->name('facturas.create');
Route::get('/facturas/{id}/preview', Facturas\Preview::class)
    ->name('facturas.preview');

// Notas de Crédito
Route::get('/notas-credito', NotasCredito\Index::class)
    ->name('notas-credito');
Route::get('/notas-credito/create', NotasCredito\Create::class)
    ->name('notas-credito.create');

// Caja
Route::get('/caja', Caja\Index::class)
    ->name('caja');
Route::get('/caja/{id}', Caja\Detalle::class)
    ->name('caja.detalle');

// Conciliación
Route::get('/conciliacion', Conciliacion\Index::class)
    ->name('conciliacion');
