<?php

use App\Livewire\Admin\Ventas\Clientes\Index as ClientesIndex;
use App\Livewire\Admin\Ventas\Pedidos\Index as PedidosIndex;
use App\Livewire\Admin\Ventas\Pedidos\Create as PedidosCreate;
use App\Livewire\Admin\Ventas\Pedidos\Edit as PedidosEdit;
use App\Livewire\Admin\Ventas\Cotizaciones\Index as CotizacionesIndex;
use App\Livewire\Admin\Ventas\Cotizaciones\Create as CotizacionesCreate;
use App\Livewire\Admin\Ventas\Cotizaciones\Edit as CotizacionesEdit;
use App\Livewire\Admin\Ventas\Cupones\Index as CuponesIndex;
use Illuminate\Support\Facades\Route;

// Clientes (modal CRUD)
Route::get('/clientes', ClientesIndex::class)
    ->middleware('permission:clientes.view')
    ->name('clientes');

// Pedidos (páginas Create/Edit)
Route::get('/pedidos', PedidosIndex::class)
    ->middleware('permission:pedidos.view')
    ->name('pedidos');

Route::get('/pedidos/crear', PedidosCreate::class)
    ->middleware('permission:pedidos.create')
    ->name('pedidos.create');

Route::get('/pedidos/{id}/editar', PedidosEdit::class)
    ->middleware('permission:pedidos.edit')
    ->name('pedidos.edit');

// Cotizaciones (páginas Create/Edit)
Route::get('/cotizaciones', CotizacionesIndex::class)
    ->middleware('permission:cotizaciones.view')
    ->name('cotizaciones');

Route::get('/cotizaciones/crear', CotizacionesCreate::class)
    ->middleware('permission:cotizaciones.create')
    ->name('cotizaciones.create');

Route::get('/cotizaciones/{id}/editar', CotizacionesEdit::class)
    ->middleware('permission:cotizaciones.edit')
    ->name('cotizaciones.edit');

// Cupones (modal CRUD)
Route::get('/cupones', CuponesIndex::class)
    ->middleware('permission:cupones.view')
    ->name('cupones');
