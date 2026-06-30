<?php

namespace App\Livewire\Traits;

use App\Services\RegionalConfigurationService;

trait HasRegionalConfiguration
{
    /**
     * Get the current regional configuration.
     */
    public function getRegionalConfigProperty(): array
    {
        return RegionalConfigurationService::getCurrentConfiguration();
    }

    /**
     * Format money using the regional settings.
     */
    public function formatMoney($amount, $includeSymbol = true): string
    {
        return format_money($amount, $includeSymbol);
    }

    /**
     * Format a date using the regional settings.
     */
    public function formatDate($date, $format = null): string
    {
        return format_date($date, $format);
    }
}
