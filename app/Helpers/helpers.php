<?php

use App\Services\RegionalConfigurationService;
use Carbon\Carbon;

if (!function_exists('userID')) {
    function userID(){
        if (auth()->user() != null) {
            return auth()->user()->id;
        } else {
            // For guest users, use session ID
            return session()->getId();
        }
    }
}

if (!function_exists('empresa')) {
    function empresa(){
        if (auth()->user() != null && auth()->user()->empresa) {
            return auth()->user()->empresa->pais->nombre ?? null;
        }
        return null;
    }
}

if (!function_exists('moneyBS')) {
    /**
     * Convert USD price to VES using latest exchange rate and format as Bs.
     */
    function moneyBS($number){
        $numericValue = is_numeric($number) ? (float)$number : 0.0;
        $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
        $bsAmount = $numericValue * $latestRate;
        return 'Bs. ' . number_format($bsAmount, 2, ',', '.');
    }
}

if (!function_exists('tasa')) {
    /**
     * Get the latest exchange rate record.
     */
    function tasa(){
        return \App\Models\ExchangeRate::latest('date')->latest('id')->first();
    }
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

        // Si la empresa es de Venezuela, por defecto es USD en primera instancia
        if (is_venezuela_company()) {
            return 'usd';
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
        $currency = strtolower(get_current_currency());

        // Si la moneda actual es Bs./VES y la empresa es de Venezuela
        if (($currency === 'bs' || $currency === 'ves') && is_venezuela_company()) {
            $currencySymbol = 'Bs.';
            $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
            $amount = $amount * $latestRate;

            // Usar formato venezolano de moneda
            $decimalSeparator = ',';
            $thousandSeparator = '.';
        } else {
            // Si la moneda seleccionada es USD, forzar el símbolo a $
            if ($currency === 'usd') {
                $currencySymbol = '$';
            } else {
                $currencySymbol = $config['currency_symbol'] ?? '$';
            }
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

if (!function_exists('format_display_price')) {
    /**
     * Formatear y mostrar precio con soporte dual y conversión según la divisa actual de la sesión.
     *
     * @param float $usdAmount Monto en USD (moneda base/original)
     * @param float $vesAmount Monto en VES (moneda de destino ya convertida usando precio_bs)
     * @return string
     */
    function format_display_price($usdAmount, $vesAmount)
    {
        $usdAmount = (float) $usdAmount;
        $vesAmount = (float) $vesAmount;
        $currency = strtolower(get_current_currency());
        $config = get_regional_config();

        if (is_venezuela_company()) {
            // Si la moneda seleccionada en la sesión es VES (Bs.)
            if ($currency === 'bs' || $currency === 'ves') {
                return 'Bs. ' . number_format($vesAmount, 2, ',', '.');
            }

            // En primera instancia (USD seleccionado / por defecto)
            // Si dual_currency es true y tiene secondary_currency
            if (!empty($config['dual_currency'])) {
                $usdFormatted = '$' . number_format($usdAmount, 2, '.', ',');
                $vesFormatted = 'Bs. ' . number_format($vesAmount, 2, ',', '.');
                return $usdFormatted ;
            }

            return '$' . number_format($usdAmount, 2, '.', ',');
        }

        // Para otros países, usar format_money estándar
        return format_money($usdAmount);
    }
}

if (!function_exists('money_product')) {
    /**
     * Helper para formatear el precio de un producto/variante según la configuración regional y precio_bs.
     *
     * @param mixed $product Producto o Variante
     * @param bool $isOffer Si se debe formatear el precio de oferta
     * @return string
     */
    function money_product($product, $isOffer = false)
    {
        if (!$product) {
            return '';
        }

        $priceField = $isOffer ? 'precio_oferta' : 'precio';
        $usdPrice = (float) ($product->$priceField ?? 0.0);

        $hasDiscount = false;
        if (isset($product->tiene_descuento)) {
            $hasDiscount = (bool) $product->tiene_descuento;
        } elseif (isset($product->precio_oferta)) {
            $hasDiscount = $product->precio_oferta !== null && $product->precio_oferta < $product->precio;
        }

        if ($isOffer && !$hasDiscount) {
            return '';
        }

        $precioBsBase = (float) ($product->precio_bs ?? $usdPrice);

        // Si es de oferta y tiene descuento, aplicar la misma proporción de descuento al precio base en Bs.
        if ($isOffer && $hasDiscount && (float)$product->precio > 0) {
            $discountRatio = (float)$product->precio_oferta / (float)$product->precio;
            $precioBsBase = $precioBsBase * $discountRatio;
        }

        $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
        $vesPrice = $precioBsBase * $latestRate;

        return format_display_price($usdPrice, $vesPrice);
    }
}

if (!function_exists('format_cart_item_price')) {
    /**
     * Formatear el precio de un item del carrito según precio_bs y la moneda seleccionada.
     *
     * @param mixed $item CartItem
     * @param bool $returnRaw Retornar el valor numérico en lugar del string formateado
     * @return mixed
     */
    function format_cart_item_price($item, $returnRaw = false)
    {
        if (!$item) {
            return $returnRaw ? 0.0 : '';
        }

        $source = $item->variant ?? $item->product;
        if (!$source) {
            return $returnRaw ? (float)$item->precio : format_money($item->precio);
        }

        $usdPrice = (float) $item->precio;
        $usdBaseProductPrice = (float) ($source->precio ?? 1.0);
        if ($usdBaseProductPrice <= 0) {
            $usdBaseProductPrice = 1.0;
        }

        $precioBsBase = (float) ($source->precio_bs ?? $usdPrice);

        // Aplicar proporción de descuento
        if ($usdPrice < $usdBaseProductPrice) {
            $discountRatio = $usdPrice / $usdBaseProductPrice;
            $precioBsBase = $precioBsBase * $discountRatio;
        }

        $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
        $vesPrice = $precioBsBase * $latestRate;

        if ($returnRaw) {
            $currency = strtolower(get_current_currency());
            if (is_venezuela_company() && ($currency === 'bs' || $currency === 'ves')) {
                return $vesPrice;
            }
            return $usdPrice;
        }

        return format_display_price($usdPrice, $vesPrice);
    }
}

if (!function_exists('format_order_item_price')) {
    /**
     * Formatear el precio de un item del pedido según precio_bs de su producto/variante y la moneda seleccionada.
     *
     * @param mixed $item OrderItem
     * @param bool $returnRaw Retornar el valor numérico en lugar del string formateado
     * @return mixed
     */
    function format_order_item_price($item, $returnRaw = false)
    {
        if (!$item) {
            return $returnRaw ? 0.0 : '';
        }

        $source = $item->variant ?? $item->product;
        $usdPrice = (float) $item->precio_unitario;

        if (!$source) {
            $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
            $vesPrice = $usdPrice * $latestRate;
            if ($returnRaw) {
                $currency = strtolower(get_current_currency());
                if (is_venezuela_company() && ($currency === 'bs' || $currency === 'ves')) {
                    return $vesPrice;
                }
                return $usdPrice;
            }
            return format_display_price($usdPrice, $vesPrice);
        }

        $usdBaseProductPrice = (float) ($source->precio ?? 1.0);
        if ($usdBaseProductPrice <= 0) {
            $usdBaseProductPrice = 1.0;
        }

        $precioBsBase = (float) ($source->precio_bs ?? $usdPrice);

        // Aplicar proporción de descuento
        if ($usdPrice < $usdBaseProductPrice) {
            $discountRatio = $usdPrice / $usdBaseProductPrice;
            $precioBsBase = $precioBsBase * $discountRatio;
        }

        $latestRate = \App\Models\ExchangeRate::getLatestRate('USD') ?? 1.0;
        $vesPrice = $precioBsBase * $latestRate;

        if ($returnRaw) {
            $currency = strtolower(get_current_currency());
            if (is_venezuela_company() && ($currency === 'bs' || $currency === 'ves')) {
                return $vesPrice;
            }
            return $usdPrice;
        }

        return format_display_price($usdPrice, $vesPrice);
    }
}

