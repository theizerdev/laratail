<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Services\ExchangeRateService;
use App\Models\ExchangeRate;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    private float $testMockRate = 39.0;

    public function test_service_can_fetch_and_store_rates_and_populate_histories(): void
    {
        // Fake the backup API response
        Http::fake([
            'https://api.exchangerate-api.com/*' => Http::response([
                'rates' => [
                    'VES' => 38.5,
                    'EUR' => 0.92,
                ]
            ], 200)
        ]);

        $service = new ExchangeRateService();
        $success = $service->fetchAndStoreRates();

        $this->assertTrue($success);

        // Assert rate was stored in main table
        $this->assertDatabaseHas('exchange_rates', [
            'usd_rate' => 38.5,
            'source' => 'backup_api',
        ]);

        // Assert daily history was created
        $this->assertDatabaseHas('exchange_rate_daily_histories', [
            'usd_rate' => 38.5,
            'source' => 'backup_api',
        ]);

        // Assert monthly history was created with correct statistics
        $this->assertDatabaseHas('exchange_rate_monthly_histories', [
            'year' => now()->year,
            'month' => now()->month,
            'usd_avg' => 38.5,
            'usd_min' => 38.5,
            'usd_max' => 38.5,
            'records_count' => 1,
        ]);
    }

    public function test_middleware_updates_exchange_rate_at_specific_intervals(): void
    {
        Cache::forget('exchange_rate_update_attempted');
        ExchangeRate::whereDate('date', today())->delete();

        $this->testMockRate = 39.0;
        Http::fake([
            'https://api.exchangerate-api.com/*' => function () {
                dump("Http::fake called. testMockRate value is: " . $this->testMockRate);
                return Http::response([
                    'rates' => [
                        'VES' => $this->testMockRate,
                        'EUR' => 0.92,
                    ]
                ], 200);
            }
        ]);

        // 1. Visit at 07:00 AM. Since no rate exists for today, it should fetch it.
        $this->travelTo(today()->setTime(7, 0, 0));
        
        $this->get('/');

        // Assert rate was fetched and stored with early fetch_time
        $rate = ExchangeRate::whereDate('date', today())->first();
        $this->assertNotNull($rate);
        $this->assertEquals(39.0, $rate->usd_rate);
        $this->assertEquals('07:00:00', $rate->fetch_time);

        // 2. Visit at 09:00 AM. Since currentTime >= 08:00:00 and fetch_time < 08:00:00, it should update.
        $this->testMockRate = 40.0;
        $this->travelTo(today()->setTime(9, 0, 0));

        $this->get('/');

        $rate->refresh();
        dump("Database usd_rate after 09:00 AM visit: " . $rate->usd_rate);
        dump("Database fetch_time after 09:00 AM visit: " . $rate->fetch_time);
        $this->assertEquals(40.0, $rate->usd_rate);
        $this->assertEquals('09:00:00', $rate->fetch_time);

        // 3. Visit at 11:00 AM. Since fetch_time >= 08:00:00 and current time < 14:00:00, it should NOT update.
        $this->testMockRate = 45.0;
        $this->travelTo(today()->setTime(11, 0, 0));

        $this->get('/');

        $rate->refresh();
        $this->assertEquals(40.0, $rate->usd_rate); // remains 40.0

        // 4. Visit at 15:00 (03:00 PM). Since currentTime >= 14:00:00 and fetch_time < 14:00:00, it should update.
        $this->testMockRate = 41.0;
        $this->travelTo(today()->setTime(15, 0, 0));

        $this->get('/');

        $rate->refresh();
        $this->assertEquals(41.0, $rate->usd_rate);
        $this->assertEquals('15:00:00', $rate->fetch_time);

        // 5. Visit at 17:00. Since fetch_time >= 14:00:00, it should NOT update.
        $this->testMockRate = 45.0;
        $this->travelTo(today()->setTime(17, 0, 0));

        $this->get('/');

        $rate->refresh();
        $this->assertEquals(41.0, $rate->usd_rate); // remains 41.0
    }

    public function test_middleware_rate_limit_on_failure(): void
    {
        // Ensure no rate exists for today and clear attempted cache
        ExchangeRate::whereDate('date', today())->delete();
        Cache::forget('exchange_rate_update_attempted');

        // Fake the backup API to return failure (401 Unauthorized)
        Http::fake([
            'https://api.exchangerate-api.com/*' => Http::response([], 401)
        ]);

        // First attempt (fails, sets cache key)
        $this->get('/');

        $this->assertTrue(Cache::has('exchange_rate_update_attempted'));

        // Changing mock to success, but it should still bypass because of the 15-minute retry limit
        Http::fake([
            'https://api.exchangerate-api.com/*' => Http::response([
                'rates' => [
                    'VES' => 42.0,
                    'EUR' => 0.92,
                ]
            ], 200)
        ]);

        $this->get('/');

        // Table should still be empty
        $this->assertDatabaseMissing('exchange_rates', [
            'usd_rate' => 42.0,
        ]);
    }

    public function test_save_rate_recalculates_monthly_history_statistics(): void
    {
        $service = new ExchangeRateService();

        // Save rate for day 1
        $service->saveRate([
            'date' => '2026-07-01',
            'usd_rate' => 35.0,
            'eur_rate' => 38.0,
            'source' => 'manual'
        ]);

        // Save rate for day 2
        $service->saveRate([
            'date' => '2026-07-02',
            'usd_rate' => 37.0,
            'eur_rate' => 39.0,
            'source' => 'manual'
        ]);

        // Check monthly history statistics: avg=(35+37)/2 = 36, min=35, max=37
        $this->assertDatabaseHas('exchange_rate_monthly_histories', [
            'year' => 2026,
            'month' => 7,
            'usd_avg' => 36.0,
            'usd_min' => 35.0,
            'usd_max' => 37.0,
            'eur_avg' => 38.5,
            'eur_min' => 38.0,
            'eur_max' => 39.0,
            'records_count' => 2,
        ]);
    }
}
