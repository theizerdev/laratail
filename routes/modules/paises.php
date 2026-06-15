<?php

use App\Livewire\Admin\Paises\Index as PaisesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/paises', PaisesIndex::class)
    ->middleware('permission:paises.view')
    ->name('paises');