<?php

namespace App\Services;

class TwoFactorService
{
    private static string $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random 16-character Base32 secret key.
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate a QR code URL for scanning into authenticator apps.
     */
    public static function getQRCodeUrl(string $label, string $secret, string $issuer = 'Laratail'): string
    {
        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($label),
            $secret,
            rawurlencode($issuer)
        );
        
        // Return secure QR server URL
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauth);
    }

    /**
     * Verify a 6-digit TOTP code against the secret key.
     * Uses a default discrepancy window of 1 (allows code from -30s to +30s of clock drift).
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $currentTimeSlice = null): bool
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = (int) floor(time() / 30);
        }

        // Clean up code input (remove spaces, etc.)
        $code = str_replace(' ', '', $code);

        if (strlen($code) !== 6 || !is_numeric($code)) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::calculateCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate 8 secure random recovery codes.
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = sprintf(
                '%s-%s',
                strtolower(self::generateSecret(5)),
                strtolower(self::generateSecret(5))
            );
        }
        return $codes;
    }

    /**
     * Calculate the TOTP code for a given secret and timeslice.
     */
    protected static function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = self::base32Decode($secret);

        // Pack time into 8-byte binary string (big-endian)
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        
        // HMAC SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        
        // Use the last nibble of the hash as offset
        $offset = ord(substr($hmac, -1)) & 0x0F;
        
        // Take 4 bytes starting from offset
        $hashpart = substr($hmac, $offset, 4);
        
        // Unpack binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        
        // Truncate to 32 bits
        $value = $value & 0x7FFFFFFF;
        
        $modulo = pow(10, 6);
        return str_pad((string) ($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a base32 encoded string.
     */
    protected static function base32Decode(string $base32): string
    {
        if (empty($base32)) {
            return '';
        }

        $base32 = strtoupper($base32);
        $paddingCharCount = substr_count($base32, '=');
        $allowedValues = [6, 4, 3, 1, 0];
        
        if (!in_array($paddingCharCount, $allowedValues)) {
            return '';
        }

        $base32 = str_replace('=', '', $base32);
        $base32 = str_split($base32);
        $binaryString = '';
        
        foreach ($base32 as $char) {
            $pos = strpos(self::$base32chars, $char);
            if ($pos === false) {
                return '';
            }
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $eightBitBytes = str_split($binaryString, 8);
        $realString = '';
        
        foreach ($eightBitBytes as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }
            $realString .= chr(bindec($byte));
        }

        return $realString;
    }
}
