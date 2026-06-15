<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Authentication routes
require __DIR__.'/auth.php';

