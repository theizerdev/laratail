<?php

use App\Livewire\Admin\Grupos\Create as GruposCreate;
use App\Livewire\Admin\Grupos\Edit as GruposEdit;
use App\Livewire\Admin\Grupos\Index as GruposIndex;
use Illuminate\Support\Facades\Route;

Route::get('/grupos', GruposIndex::class)
    ->middleware('permission:groups.view')
    ->name('grupos');

Route::get('/grupos/create', GruposCreate::class)
    ->middleware('permission:groups.create')
    ->name('grupos.create');

Route::get('/grupos/{id}/edit', GruposEdit::class)
    ->middleware('permission:groups.edit')
    ->name('grupos.edit');
