<?php

namespace Tests\Feature;

use App\Services\TwoFactorService;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    /**
     * Test that TwoFactorService can generate a 16-character base32 secret.
     */
    public function test_secret_generation(): void
    {
        $secret = TwoFactorService::generateSecret();
        $this->assertEquals(16, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    /**
     * Test that TwoFactorService can generate a QR code URL.
     */
    public function test_qr_code_url(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQ'; // standard base32
        $url = TwoFactorService::getQRCodeUrl('test@example.com', $secret, 'Laratail');
        
        $this->assertStringContainsString('https://api.qrserver.com', $url);
        $this->assertStringContainsString($secret, $url);
        $this->assertStringContainsString('test%2540example.com', $url);
    }

    /**
     * Test TOTP verification logic.
     */
    public function test_totp_code_verification(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQ';
        
        // Since TOTP is time-sensitive, we'll calculate the code for a specific time slice
        // and verify it against that same slice.
        $timeSlice = 12345678; // static slice
        
        // We will call calculateCode via reflection since it is protected
        $reflector = new \ReflectionClass(TwoFactorService::class);
        $method = $reflector->getMethod('calculateCode');
        $method->setAccessible(true);
        
        $code = $method->invoke(null, $secret, $timeSlice);
        
        $this->assertEquals(6, strlen($code));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        
        // Verify with the exact time slice (discrepancy 0)
        $this->assertTrue(TwoFactorService::verifyCode($secret, $code, 0, $timeSlice));
        
        // Verify with discrepancy 1 (drift of +/- 1 time slice)
        $this->assertTrue(TwoFactorService::verifyCode($secret, $code, 1, $timeSlice - 1));
        $this->assertTrue(TwoFactorService::verifyCode($secret, $code, 1, $timeSlice + 1));
        
        // Verify drift outside the discrepancy window fails
        $this->assertFalse(TwoFactorService::verifyCode($secret, $code, 0, $timeSlice - 1));
        $this->assertFalse(TwoFactorService::verifyCode($secret, $code, 1, $timeSlice - 2));
    }

    /**
     * Test that TwoFactorService can generate secure recovery codes.
     */
    public function test_recovery_codes_generation(): void
    {
        $codes = TwoFactorService::generateRecoveryCodes(8);
        
        $this->assertCount(8, $codes);
        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[a-z2-7]{5}-[a-z2-7]{5}$/', $code);
        }
    }
}
