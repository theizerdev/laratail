<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Services\RegionalConfigurationService;
use App\Models\Empresa;
use App\Models\Pais;

class RegionalConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RegionalConfigurationService::class, function ($app) {
            return new RegionalConfigurationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar helpers globales
        $this->registerGlobalHelpers();

        // Registrar eventos para cambios de empresa
        $this->registerEvents();
    }

    /**
     * Registrar helpers globales para configuración regional
     */
    private function registerGlobalHelpers(): void
    {
        // Las funciones helpers ya están cargadas en app/Helpers/helpers.php
        // No es necesario hacer nada aquí ya que Composer las carga automáticamente
    }

    /**
     * Registrar eventos para cambios de configuración
     */
    private function registerEvents(): void
    {
        // Evento cuando se cambia la empresa seleccionada
        \Illuminate\Support\Facades\Event::listen(
            'empresa.changed',
            function ($empresa) {
                RegionalConfigurationService::setRegionalConfiguration($empresa);
            }
        );
    }
}
