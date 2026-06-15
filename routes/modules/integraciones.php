<?php

use App\Livewire\Admin\Integraciones\Index as IntegracionesIndex;
use App\Livewire\Admin\Integraciones\WhatsAppCrm;
use Illuminate\Support\Facades\Route;

Route::get('/integraciones', IntegracionesIndex::class)
    ->middleware('permission:integraciones.view')
    ->name('integraciones');

Route::get('/integraciones/whatsapp', WhatsAppCrm::class)
    ->middleware('permission:whatsapp.view')
    ->name('integraciones.whatsapp');
