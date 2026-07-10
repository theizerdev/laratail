<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ExchangeRateService;
use App\Models\ExchangeRate;

class UpdateExchangeRateMiddleware
{
    protected ExchangeRateService $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function handle(Request $request, Closure $next)
    {
        $todayRate = ExchangeRate::whereDate('date', today())->first();

        $needsUpdate = false;

        if (!$todayRate) {
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            // Para evitar saturar las APIs externas o ralentizar cada petición si la API está caída,
            // limitamos los reintentos a una vez cada 15 minutos.
            if (!Cache::has('exchange_rate_update_attempted')) {
                try {
                    $success = $this->exchangeRateService->fetchAndStoreRates();

                    if ($success) {
                        Log::info('Tasa de cambio del día actualizada automáticamente usando el servicio.');
                    } else {
                        // Registrar el intento fallido en caché por 15 minutos
                        Cache::put('exchange_rate_update_attempted', true, now()->addMinutes(15));
                        Log::error('Fallo al actualizar automáticamente la tasa de cambio a través del middleware.');
                    }
                } catch (\Exception $e) {
                    Cache::put('exchange_rate_update_attempted', true, now()->addMinutes(15));
                    Log::error('Excepción al intentar actualizar la tasa de cambio en el middleware: ' . $e->getMessage());
                }
            }
        }

        return $next($request);
    }
}