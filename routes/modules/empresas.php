<?php

use App\Livewire\Admin\Empresas\Create as EmpresasCreate;
use App\Livewire\Admin\Empresas\Edit as EmpresasEdit;
use App\Livewire\Admin\Empresas\Index as EmpresasIndex;
use Illuminate\Support\Facades\Route;

Route::get('/empresas', EmpresasIndex::class)
    ->middleware('permission:empresas.view')
    ->name('empresas');

Route::get('/empresas/create', EmpresasCreate::class)
    ->middleware('permission:empresas.create')
    ->name('empresas.create');

Route::get('/empresas/{id}/edit', EmpresasEdit::class)
    ->middleware('permission:empresas.edit')
    ->name('empresas.edit');
