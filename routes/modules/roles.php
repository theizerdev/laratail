<?php

use App\Livewire\Admin\Roles\Create as RolesCreate;
use App\Livewire\Admin\Roles\Edit as RolesEdit;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/roles', RolesIndex::class)
    ->middleware('permission:roles.view')
    ->name('roles');

Route::get('/roles/create', RolesCreate::class)
    ->middleware('permission:roles.create')
    ->name('roles.create');

Route::get('/roles/{id}/edit', RolesEdit::class)
    ->middleware('permission:roles.edit')
    ->name('roles.edit');