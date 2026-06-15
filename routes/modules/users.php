<?php

use App\Livewire\Admin\Users\Create as UsersCreate;
use App\Livewire\Admin\Users\Edit as UsersEdit;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::get('/users', UsersIndex::class)
    ->middleware('permission:users.view')
    ->name('users');

Route::get('/users/create', UsersCreate::class)
    ->middleware('permission:users.create')
    ->name('users.create');

Route::get('/users/{id}/edit', UsersEdit::class)
    ->middleware('permission:users.edit')
    ->name('users.edit');