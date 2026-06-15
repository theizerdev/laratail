<?php

use App\Livewire\Admin\Sucursales\Create as SucursalesCreate;
use App\Livewire\Admin\Sucursales\Edit as SucursalesEdit;
use App\Livewire\Admin\Sucursales\Index as SucursalesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/sucursales', SucursalesIndex::class)
    ->middleware('permission:sucursales.view')
    ->name('sucursales');

Route::get('/sucursales/create', SucursalesCreate::class)
    ->middleware('permission:sucursales.create')
    ->name('sucursales.create');

Route::get('/sucursales/{id}/edit', SucursalesEdit::class)
    ->middleware('permission:sucursales.edit')
    ->name('sucursales.edit');
