<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GoogleAuthService — Port of Node.js Google OAuth token verification.
 *
 * Verifies Google ID tokens from Flutter app sign-in.
 */
class GoogleAuthService
{
    private string $clientId;

    public function __construct()
    {
        $this->clientId = config('services.google.client_id', '');
    }

    /**
     * Verify a Google ID token and return the payload.
     *
     * @throws \Exception if token is invalid
     */
    public function verifyIdToken(string $idToken): array
    {
        // If local environment and token is mock/dummy, return mock payload
        if (config('app.env') === 'local' && (str_starts_with($idToken, 'mock_') || $idToken === 'dummy_token' || empty($idToken))) {
            return [
                'googleId'    => 'mock_google_id_12345',
                'email'       => 'mockuser@gmail.com',
                'name'        => 'Mock User',
                'displayName' => 'Mock User',
                'picture'     => '',
                'given_name'  => 'Mock',
                'family_name' => 'User',
            ];
        }

        try {
            // Use Google's tokeninfo endpoint for verification
            $response = Http::timeout(10)->withoutVerifying()->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken
            ]);

            if ($response->successful()) {
                $payload = $response->json();

                // Verify the audience matches our client ID
                $aud = $payload['aud'] ?? '';
                if ($aud !== $this->clientId) {
                    // Also allow iOS client IDs (ending with .apps.googleusercontent.com)
                    if (!str_ends_with($aud, '.apps.googleusercontent.com')) {
                        throw new \Exception('Google token audience mismatch');
                    }
                }

                // Verify token is not expired
                $exp = $payload['exp'] ?? 0;
                if ($exp < time()) {
                    throw new \Exception('Google token has expired');
                }

                return [
                    'googleId'    => $payload['sub'] ?? '',
                    'email'       => $payload['email'] ?? '',
                    'name'        => $payload['name'] ?? '',
                    'displayName' => $payload['name'] ?? '',
                    'picture'     => $payload['picture'] ?? '',
                    'given_name'  => $payload['given_name'] ?? '',
                    'family_name' => $payload['family_name'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Google token verification request failed: ' . $e->getMessage());
        }

        // In local environment, if Google tokeninfo request fails (e.g. no internet, expired/mock token),
        // we can return a mock user so the local app doesn't break for local development testing.
        if (config('app.env') === 'local') {
            Log::info('GoogleAuthService: Falling back to mock user in local development');
            return [
                'googleId'    => 'mock_google_id_12345',
                'email'       => 'mockuser@gmail.com',
                'name'        => 'Mock User',
                'displayName' => 'Mock User',
                'picture'     => '',
                'given_name'  => 'Mock',
                'family_name' => 'User',
            ];
        }

        throw new \Exception('Invalid Google ID token');
    }
}
