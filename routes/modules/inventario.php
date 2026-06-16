<?php

use App\Livewire\Admin\Inventario\Proveedores\Index as ProveedoresIndex;
use App\Livewire\Admin\Inventario\Movimientos\Index as MovimientosIndex;
use App\Livewire\Admin\Inventario\Kardex\Index as KardexIndex;
use App\Livewire\Admin\Inventario\AlertasStock\Index as AlertasStockIndex;
use App\Livewire\Admin\Inventario\OrdenesCompra\Index as OrdenesCompraIndex;
use App\Livewire\Admin\Inventario\OrdenesCompra\Create as OrdenesCompraCreate;
use App\Livewire\Admin\Inventario\OrdenesCompra\Edit as OrdenesCompraEdit;
use App\Livewire\Admin\Inventario\OrdenesCompra\Recibir as OrdenesCompraRecibir;
use App\Livewire\Admin\Inventario\Envios\Index as EnviosIndex;
use App\Livewire\Admin\Inventario\Envios\Create as EnviosCreate;
use App\Livewire\Admin\Inventario\Envios\Edit as EnviosEdit;
use App\Livewire\Admin\Inventario\Envios\GuiaDespacho as EnviosGuiaDespacho;
use Illuminate\Support\Facades\Route;

// Proveedores (modal CRUD)
Route::get('/proveedores', ProveedoresIndex::class)
    ->middleware('permission:proveedores.view')
    ->name('proveedores');

// Movimientos de Inventario (modal CRUD)
Route::get('/movimientos', MovimientosIndex::class)
    ->middleware('permission:movimientos.view')
    ->name('movimientos');

// Kardex (read-only page)
Route::get('/kardex', KardexIndex::class)
    ->middleware('permission:kardex.view')
    ->name('kardex');

// Alertas de Stock (read-only page)
Route::get('/alertas-stock', AlertasStockIndex::class)
    ->middleware('permission:alertas_stock.view')
    ->name('alertas-stock');

// Órdenes de Compra (páginas Create/Edit)
Route::get('/ordenes-compra', OrdenesCompraIndex::class)
    ->middleware('permission:ordenes_compra.view')
    ->name('ordenes-compra');

Route::get('/ordenes-compra/crear', OrdenesCompraCreate::class)
    ->middleware('permission:ordenes_compra.create')
    ->name('ordenes-compra.create');

Route::get('/ordenes-compra/{id}/editar', OrdenesCompraEdit::class)
    ->middleware('permission:ordenes_compra.edit')
    ->name('ordenes-compra.edit');

Route::get('/ordenes-compra/{id}/recibir', OrdenesCompraRecibir::class)
    ->middleware('permission:ordenes_compra.recibir')
    ->name('ordenes-compra.recibir');

// Envíos (páginas Create/Edit)
Route::get('/envios', EnviosIndex::class)
    ->middleware('permission:envios.view')
    ->name('envios');

Route::get('/envios/crear', EnviosCreate::class)
    ->middleware('permission:envios.create')
    ->name('envios.create');

Route::get('/envios/{id}/editar', EnviosEdit::class)
    ->middleware('permission:envios.edit')
    ->name('envios.edit');

Route::get('/envios/{id}/guia-despacho', EnviosGuiaDespacho::class)
    ->middleware('permission:envios.guia')
    ->name('envios.guia-despacho');
