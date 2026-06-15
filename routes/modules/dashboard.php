<?php

use App\Livewire\Admin\Dashboard\Index as Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', Dashboard::class)->name('dashboard');
