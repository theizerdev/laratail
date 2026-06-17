<?php

use Illuminate\Support\Facades\Route;

Route::get('/empleados', \App\Livewire\Admin\Empleados\Index::class)
 ->middleware('permission:empleados.view')
->name('empleados.index');
