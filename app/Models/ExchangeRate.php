<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = [
        'date',
        'usd_rate',
        'eur_rate',
        'source',
        'fetch_time',
        'raw_data',
    ];

    protected $casts = [
        'date' => 'date',
        'usd_rate' => 'float',
        'eur_rate' => 'float',
        'raw_data' => 'array',
    ];

    /**
     * Get the latest exchange rate for a given currency (defaults to VES rate relative to USD).
     */
    public static function getLatestRate(string $currency = 'USD'): ?float
    {
        $rate = self::latest('date')->latest('id')->first();
        return $rate ? (float) $rate->usd_rate : null;
    }

    /**
     * Get the today exchange rate model.
     */
    public static function getTodayRate()
    {
        return self::whereDate('date', today())->latest('id')->first() 
            ?? self::latest('date')->latest('id')->first();
    }

    /**
     * Get today's rates list.
     */
    public static function getTodayRates()
    {
        return self::whereDate('date', today())->get();
    }
}
