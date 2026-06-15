<?php

use App\Livewire\Admin\Catalogo\Categorias\Index as CategoriasIndex;
use App\Livewire\Admin\Catalogo\Marcas\Index as MarcasIndex;
use App\Livewire\Admin\Catalogo\Productos\Index as ProductosIndex;
use App\Livewire\Admin\Catalogo\Productos\Create as ProductosCreate;
use App\Livewire\Admin\Catalogo\Productos\Edit as ProductosEdit;
use App\Livewire\Admin\Catalogo\Atributos\Index as AtributosIndex;
use Illuminate\Support\Facades\Route;

// Categorías (modal CRUD)
Route::get('/categorias', CategoriasIndex::class)
    ->middleware('permission:categorias.view')
    ->name('categorias');

// Marcas (modal CRUD)
Route::get('/marcas', MarcasIndex::class)
    ->middleware('permission:marcas.view')
    ->name('marcas');

// Productos (páginas Create/Edit separadas)
Route::get('/productos', ProductosIndex::class)
    ->middleware('permission:productos.view')
    ->name('productos');

Route::get('/productos/create', ProductosCreate::class)
    ->middleware('permission:productos.create')
    ->name('productos.create');

Route::get('/productos/{id}/edit', ProductosEdit::class)
    ->middleware('permission:productos.edit')
    ->name('productos.edit');

// Atributos (modal CRUD)
Route::get('/atributos', AtributosIndex::class)
    ->middleware('permission:atributos.view')
    ->name('atributos');
