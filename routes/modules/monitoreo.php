<?php

use App\Models\Backup;
use App\Livewire\Admin\Monitoreo\Index as MonitoreoIndex;
use App\Livewire\Admin\Monitoreo\BaseDatos;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/monitoreo', MonitoreoIndex::class)
    ->middleware('permission:monitoreo.view')
    ->name('monitoreo');

Route::get('/monitoreo/base-datos', BaseDatos::class)
    ->middleware('permission:monitoreo.database')
    ->name('monitoreo.base-datos');

Route::get('/monitoreo/backup-download/{backup}', function (Backup $backup) {
    if (! Storage::disk($backup->disk)->exists($backup->path)) {
        abort(404, 'Archivo no encontrado');
    }

    $fullPath = Storage::disk($backup->disk)->path($backup->path);

    return response()->download($fullPath, $backup->original_name);
})
    ->middleware('permission:monitoreo.backup')
    ->name('monitoreo.backup-download');
