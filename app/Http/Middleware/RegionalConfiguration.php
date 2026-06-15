<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RegionalConfigurationService;

class RegionalConfiguration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //dd(auth()->user()->empresa);
        // Aplicar configuración regional si el usuario está autenticado
        if (auth()->check()) {
            RegionalConfigurationService::setRegionalConfiguration();
        } else {
            // Para usuarios no autenticados (frontend público), usar la primera empresa
            $empresa = \App\Models\Empresa::with('pais')->first();
            if ($empresa) {
                RegionalConfigurationService::setRegionalConfiguration($empresa);
            }
        }

        return $next($request);
    }
}
