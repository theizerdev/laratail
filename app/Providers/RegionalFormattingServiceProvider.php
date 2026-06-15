<?php

namespace App\Providers;

use App\Services\RegionalConfigurationService;
use Illuminate\Support\ServiceProvider;

class RegionalFormattingServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Registrar helpers para Blade
        \Blade::directive('money', function ($expression) {
            return "<?php echo app(\App\Services\RegionalConfigurationService::class)->formatMoney($expression); ?>";
        });

        \Blade::directive('date', function ($expression) {
            return "<?php echo app(\App\Services\RegionalConfigurationService::class)->formatDate($expression); ?>";
        });

        \Blade::directive('datetime', function ($expression) {
            return "<?php echo app(\App\Services\RegionalConfigurationService::class)->formatDateTime($expression); ?>";
        });

        // Helper functions globales
        if (!function_exists('format_money')) {
            function format_money($amount, $includeSymbol = true) {
                return app(RegionalConfigurationService::class)->formatMoney($amount, $includeSymbol);
            }
        }

        if (!function_exists('format_date')) {
            function format_date($date, $format = null) {
                return app(RegionalConfigurationService::class)->formatDate($date, $format);
            }
        }

        if (!function_exists('format_datetime')) {
            function format_datetime($date, $includeTime = true) {
                return app(RegionalConfigurationService::class)->formatDateTime($date, $includeTime);
            }
        }
    }
}
