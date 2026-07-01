<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppService — Port of Node.js Meta WhatsApp OTP logic.
 *
 * Sends OTP via Meta WhatsApp Business API.
 */
class WhatsAppService
{
    private string $accessToken;
    private string $phoneNumberId;
    private string $apiVersion;
    private string $templateName;
    private string $templateLang;
    private bool $hasButton;

    public function __construct()
    {
        $this->accessToken   = config('services.whatsapp.access_token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->apiVersion    = config('services.whatsapp.api_version', 'v21.0');
        $this->templateName  = config('services.whatsapp.template_name', 'text');
        $this->templateLang  = config('services.whatsapp.template_lang', 'en_US');
        $this->hasButton     = config('services.whatsapp.has_button', false);
    }

    /**
     * Send an OTP to a WhatsApp number.
     * Returns true on success, throws on failure.
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        // Normalize phone: remove spaces, dashes, ensure +91 prefix for India
        $normalizedPhone = $this->normalizePhone($phone);

        // Use 'text' template mode → send a direct text message
        if ($this->templateName === 'text') {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $normalizedPhone,
                'type'              => 'text',
                'text'              => [
                    'body' => "Your Amantran verification code is: *{$otp}*\n\nThis OTP is valid for 10 minutes. Do not share it with anyone."
                ]
            ];
        } else {
            // Template-based message
            $components = [
                [
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $otp]
                    ]
                ]
            ];

            if ($this->hasButton) {
                $components[] = [
                    'type'     => 'button',
                    'sub_type' => 'url',
                    'index'    => '0',
                    'parameters' => [
                        ['type' => 'text', 'text' => $otp]
                    ]
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $normalizedPhone,
                'type'              => 'template',
                'template'          => [
                    'name'     => $this->templateName,
                    'language' => ['code' => $this->templateLang],
                    'components' => $components
                ]
            ];
        }

        $response = Http::withToken($this->accessToken)
            ->timeout(5)
            ->withoutVerifying()
            ->post($url, $payload);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Unknown WhatsApp API error');
            Log::error('WhatsApp OTP send failed', [
                'phone'  => $normalizedPhone,
                'error'  => $error,
                'status' => $response->status(),
            ]);
            throw new \Exception("WhatsApp send failed: {$error}");
        }

        Log::info('WhatsApp OTP sent', ['phone' => $normalizedPhone]);
        return true;
    }

    /**
     * Normalize a phone number to E.164 format.
     */
    public function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // If starts with 0, remove leading 0 and add India country code
        if (str_starts_with($digits, '0')) {
            $digits = '91' . substr($digits, 1);
        }

        // If not already starting with country code, add India +91
        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }

        return $digits; // WhatsApp API expects digits without +
    }
}
