<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Sends push notifications to all registered devices of a dealer,
     * and records the notification history in the notifications table.
     *
     * @param Member $dealer The recipient dealer
     * @param string $title Notification title
     * @param string $body Notification body text
     * @param array $data Optional custom key-value payload data
     * @return bool Returns true if processed successfully
     */
    public static function sendPushNotification(Member $dealer, string $title, string $body, array $data = []): bool
    {
        try {
            // 1. Persist the notification in database history
            Notification::create([
                'member_id' => $dealer->id,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'is_read' => false,
            ]);

            // 2. Fetch all registered device FCM tokens
            $tokens = $dealer->devices()->pluck('fcm_token')->toArray();

            if (empty($tokens)) {
                Log::info("FCM Service: Skipping push notification for Member ID {$dealer->id} (No device tokens registered).");
                return true;
            }

            // 3. Check for Firebase Service Account Credentials in Environment
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if (empty($credentialsPath)) {
                $credentialsPath = storage_path('app/firebase/service-account.json');
            } else {
                // If it is a relative path defined in .env, resolve it relative to base path
                if (!str_starts_with($credentialsPath, '/') && !str_starts_with($credentialsPath, '\\') && !preg_match('/^[a-zA-Z]:\\\\/', $credentialsPath)) {
                    $credentialsPath = base_path($credentialsPath);
                }
            }

            if (!file_exists($credentialsPath)) {
                Log::info("FCM Service [SIMULATION MODE] for Member ID {$dealer->id}:", [
                    'tokens' => $tokens,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data
                ]);
                return true;
            }

            // 4. Load credentials and fetch OAuth2 access token
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            if (!$credentials || !isset($credentials['private_key']) || !isset($credentials['project_id'])) {
                Log::error("FCM Service: Invalid service-account.json credentials format.");
                return false;
            }

            $projectId = $credentials['project_id'];
            $accessToken = self::getAccessToken($credentials);

            if (empty($accessToken)) {
                Log::error("FCM Service: Failed to generate OAuth2 Access Token for Firebase.");
                return false;
            }

            // 5. Send FCM HTTP v1 notifications (Requires sending one request per token)
            $successCount = 0;
            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_map('strval', array_merge($data, [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ])),
                    ]
                ];

                $response = Http::withToken($accessToken)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $status = $response->status();
                    $errorData = $response->json();

                    Log::warning("FCM Service: Failed to send push to token {$token} for Member ID {$dealer->id}.", [
                        'status' => $status,
                        'response' => $errorData
                    ]);

                    // 6. Clean up stale or invalid token (HTTP 404/410 or UNREGISTERED)
                    $isUnregistered = false;
                    if ($status === 404 || $status === 410) {
                        $isUnregistered = true;
                    } elseif (isset($errorData['error']['details'])) {
                        foreach ($errorData['error']['details'] as $detail) {
                            if (isset($detail['@type']) && str_contains($detail['@type'], 'status') && isset($detail['status']) && $detail['status'] === 'UNREGISTERED') {
                                $isUnregistered = true;
                                break;
                            }
                        }
                    }

                    if ($isUnregistered) {
                        $dealer->devices()->where('fcm_token', $token)->delete();
                        Log::info("FCM Service: Cleaned up invalid token {$token} for Member ID {$dealer->id}.");
                    }
                }
            }

            Log::info("FCM Service: Finished dispatch. Sent successfully to {$successCount} of " . count($tokens) . " devices for Member ID {$dealer->id}.");
            return $successCount > 0;

        } catch (\Exception $e) {
            Log::error("FCM Service Exception: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Generate OAuth2 Access Token using RS256 signing of JWT.
     * Zero-dependency pure PHP implementation using openssl_sign.
     */
    private static function getAccessToken(array $credentials): ?string
    {
        try {
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            
            $now = time();
            $payload = json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600
            ]);

            $base64UrlHeader = self::base64UrlEncode($header);
            $base64UrlPayload = self::base64UrlEncode($payload);

            $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;
            $signature = '';

            if (!openssl_sign($signatureInput, $signature, $credentials['private_key'], 'SHA256')) {
                Log::error("FCM Service JWT Signature: openssl_sign failed.");
                return null;
            }

            $base64UrlSignature = self::base64UrlEncode($signature);
            $jwt = $signatureInput . '.' . $base64UrlSignature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error("FCM Service OAuth2 Token Request failed: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("FCM Service getAccessToken Exception: " . $e->getMessage());
            return null;
        }
    }

    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
