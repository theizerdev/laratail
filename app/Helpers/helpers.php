<?php

use App\Services\RegionalConfigurationService;
use Carbon\Carbon;

function userID(){

    if (auth()->user() != null) {
        return auth()->user()->id;
    } else {
        // For guest users, use session ID
        return session()->getId();
    }

}
function empresa(){

    if (auth()->user() != null) {

        return auth()->user()->empresa->pais->name;
    }
}
// Devolver numero en formato moneda
function money($number){
    // Asegurarse de que el número sea numérico antes de formatearlo
    $numericValue = is_numeric($number) ? $number : 0;
    return '$'.number_format($numericValue, 2, ',', '.');
}

// Devolver numero en formato moneda
function moneyBS($number){
    // Asegurarse de que el número sea numérico antes de formatearlo
    $numericValue = is_numeric($number) ? $number : 0;
    return ' Bs. '.number_format($numericValue, 2, ',', '.');
}

function tasa(){
    // Obtener la última tasa de cambio del día
    return \App\Models\ExchangeRate::whereDate('created_at', Carbon::now())
        ->latest('created_at')
        ->first();
}

if (!function_exists('get_regional_config')) {
    /**
     * Obtener la configuración regional actual
     *
     * @param string|null $key Clave específica de la configuración
     * @return mixed Configuración completa o valor específico
     */
    function get_regional_config($key = null)
    {
        $config = RegionalConfigurationService::getCurrentConfiguration();

        if ($key) {
            return $config[$key] ?? null;
        }

        return $config;
    }
}

if (!function_exists('get_current_currency')) {
    /**
     * Obtener la moneda actual
     *
     * @return string
     */
    function get_current_currency()
    {
        // Primero intentar obtener la moneda de la sesión
        if (session()->has('currency')) {
            return session('currency');
        }
        // Si no hay moneda en la sesión, usar la configuración regional
        return get_regional_config('currency') ?? 'usd';
    }
}

if (!function_exists('get_current_timezone')) {
    /**
     * Obtener la zona horaria actual
     *
     * @return string
     */
    function get_current_timezone()
    {
        return get_regional_config('timezone') ?? 'UTC';
    }
}

if (!function_exists('get_current_locale')) {
    /**
     * Obtener el idioma actual
     *
     * @return string
     */
    function get_current_locale()
    {
        return get_regional_config('locale') ?? 'es';
    }
}

if (!function_exists('get_current_date_format')) {
    /**
     * Obtener el formato de fecha actual
     *
     * @return string
     */
    function get_current_date_format()
    {
        return get_regional_config('date_format') ?? 'd/m/Y';
    }
}

if (!function_exists('get_current_currency_symbol')) {
    /**
     * Obtener el símbolo de moneda actual
     *
     * @return string
     */
    function get_current_currency_symbol()
    {
        return get_regional_config('currency_symbol') ?? '$';
    }
}

if (!function_exists('format_money')) {
    /**
     * Formatear una cantidad de dinero según la configuración regional
     *
     * @param float $amount Cantidad a formatear
     * @param bool $includeSymbol Incluir símbolo de moneda
     * @return string
     */
    function format_money($amount, $includeSymbol = true)
    {
        if (is_null($amount)) {
            $amount = 0;
        }

        $amount = (float) $amount;

        // Obtener configuración regional
        $config = get_regional_config();

        $decimals = $config['decimals'] ?? 2;
        $decimalSeparator = $config['decimal_separator'] ?? '.';
        $thousandSeparator = $config['thousand_separator'] ?? ',';

        // Obtener la moneda actual de la sesión
        $currency = get_current_currency();

        // Definir el símbolo de moneda según la moneda seleccionada
        if ($currency === 'bs') {
           $currencySymbol = 'Bs.';
        } else {
            $currencySymbol = '$';
        }

        $formatted = number_format($amount, $decimals, $decimalSeparator, $thousandSeparator);

        return $includeSymbol ? $currencySymbol . ' ' . $formatted : $formatted;
    }
}

if (!function_exists('format_date')) {
    /**
     * Formatear una fecha según la configuración regional
     *
     * @param mixed $date Fecha a formatear
     * @param string|null $format Formato específico (opcional)
     * @return string
     */
    function format_date($date, $format = null)
    {
        if (is_null($date)) {
            return '';
        }

        try {
            $carbonDate = Carbon::parse($date);

            if ($format) {
                return $carbonDate->format($format);
            }

            $dateFormat = get_regional_config('date_format') ?? 'd/m/Y';

            // Convertir formato del país a formato Carbon
            $carbonFormat = match($dateFormat) {
                'dd/mm/yyyy' => 'd/m/Y',
                'mm/dd/yyyy' => 'm/d/Y',
                'yyyy-mm-dd' => 'Y-m-d',
                'dd-mm-yyyy' => 'd-m-Y',
                'yyyy/mm/dd' => 'Y/m/d',
                default => 'd/m/Y'
            };

            return $carbonDate->format($carbonFormat);
        } catch (Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('money')) {
    /**
     * Alias para format_money con símbolo incluido
     *
     * @param float $amount
     * @return string
     */
    function money($amount)
    {
        return format_money($amount, true);
    }
}

if (!function_exists('is_venezuela_company')) {
    /**
     * Verificar si la empresa actual es de Venezuela
     *
     * @return bool
     */
    function is_venezuela_company()
    {
        $config = get_regional_config();
        return isset($config['currency']) && $config['currency'] === 'VES';
    }
}

if (!function_exists('format_dual_currency')) {
    /**
     * Formatear monto en doble moneda para Venezuela (USD y Bs.)
     *
     * @param float $amount Monto en USD (moneda base del sistema)
     * @param bool $showBoth Mostrar ambas monedas
     * @return string
     */
    function format_dual_currency($amount, $showBoth = true)
    {
        $amountValue = (float) $amount;
        $config = get_regional_config();

        if (is_venezuela_company()) {
            // Para Venezuela, siempre mostrar USD como principal
            $usdFormatted = '$' . number_format($amountValue, 2, '.', ',');

            if (!$showBoth) {
                return $usdFormatted;
            }
        } else {
            // Para otros países, usar su configuración regional
            $symbol = $config['currency_symbol'] ?? '$';
            $decimals = $config['decimals'] ?? 2;
            $decimalSep = $config['decimal_separator'] ?? '.';
            $thousandSep = $config['thousand_separator'] ?? ',';

            return $symbol . number_format($amountValue, $decimals, $decimalSep, $thousandSep);
        }

        // Para Venezuela con doble moneda

        // Obtener tasa de cambio actual
        $exchangeRate = \App\Models\ExchangeRate::getLatestRate('USD');

        if ($exchangeRate) {
            $bsAmount = $amountValue * $exchangeRate;
            $bsFormatted = 'Bs. ' . number_format($bsAmount, 2, ',', '.');
            return $usdFormatted . ' / ' . $bsFormatted;
        }

        return $usdFormatted;
    }
}

if (!function_exists('money_dual')) {
    /**
     * Directiva para mostrar moneda dual en Venezuela
     *
     * @param float $amount
     * @return string
     */
    function money_dual($amount)
    {
        return format_dual_currency($amount, true);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Formatear fecha y hora según la configuración regional
     *
     * @param mixed $datetime Fecha y hora a formatear
     * @param bool $includeTime Incluir la hora
     * @return string
     */
    function format_datetime($datetime, $includeTime = true)
    {
        if (is_null($datetime)) {
            return '';
        }

        try {
            $carbonDate = Carbon::parse($datetime);

            if (!$includeTime) {
                return format_date($datetime);
            }

            $dateFormat = get_regional_config('date_format') ?? 'd/m/Y';

            // Convertir formato del país a formato Carbon con hora
            $carbonFormat = match($dateFormat) {
                'dd/mm/yyyy' => 'd/m/Y H:i',
                'mm/dd/yyyy' => 'm/d/Y H:i',
                'yyyy-mm-dd' => 'Y-m-d H:i',
                'dd-mm-yyyy' => 'd-m-Y H:i',
                'yyyy/mm/dd' => 'Y/m/d H:i',
                default => 'd/m/Y H:i'
            };

            return $carbonDate->format($carbonFormat);
        } catch (Exception $e) {
            return (string) $datetime;
        }
    }

    if (!function_exists('secure_storage_url')) {
    function secure_storage_url($path)
    {
        if (!$path) {
            return null;
        }

        $url = Storage::url($path);

        // Forzar HTTPS si la URL actual es HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return str_replace('http://', 'https://', $url);
        }

        // También verificar el header X-Forwarded-Proto (para proxies/load balancers)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return str_replace('http://', 'https://', $url);
        }

        // Verificar si la URL actual contiene https
        if (strpos(url('/'), 'https://') === 0) {
            return str_replace('http://', 'https://', $url);
        }

        return $url;
    }
}
}
