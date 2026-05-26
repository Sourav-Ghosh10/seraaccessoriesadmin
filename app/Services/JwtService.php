<?php

namespace App\Services;

use Exception;

class JwtService
{
    /**
     * Generate a JWT token.
     *
     * @param array $payload
     * @param int $expirySeconds
     * @return string
     */
    public static function generateToken(array $payload, int $expirySeconds): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + $expirySeconds;

        $encodedHeader = self::base64UrlEncode(json_encode($header));
        $encodedPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", self::getSecret(), true);
        $encodedSignature = self::base64UrlEncode($signature);

        return "$encodedHeader.$encodedPayload.$encodedSignature";
    }

    /**
     * Decode and validate a JWT token.
     *
     * @param string $token
     * @return array|null Null if invalid or expired
     */
    public static function decodeAndValidateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        // Verify Signature
        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", self::getSecret(), true);
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return null; // Signature invalid
        }

        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);
        if (!$payload) {
            return null;
        }

        // Verify Expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // Expired
        }

        return $payload;
    }

    /**
     * Helper to encode to Base64Url
     */
    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Helper to decode from Base64Url
     */
    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    /**
     * Retrieve the signing secret (defaults to app.key)
     */
    private static function getSecret(): string
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }
        return $key;
    }
}
