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
        // Use Google's tokeninfo endpoint for verification
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken
        ]);

        if ($response->failed()) {
            throw new \Exception('Invalid Google ID token');
        }

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
}
