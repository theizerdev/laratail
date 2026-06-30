<?php

use App\Livewire\Admin\Tasas\Index as TasasIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasas', TasasIndex::class)
    ->middleware('permission:paises.view')
    ->name('tasas');
