<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslateApiController extends Controller
{
    /**
     * POST /api/translate
     */
    public function translate(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required',
            ]);

            $text       = $request->text;
            $sourceCode = $request->sourceCode ?? 'auto';
            $targetCode = $request->targetCode ?? 'en';

            // Resolve target language code if name is passed
            $targetLangCode = $this->resolveLanguageCode($targetCode);
            $sourceLangCode = $this->resolveLanguageCode($sourceCode);

            $url = "https://translate.googleapis.com/translate_a/single";
            
            $response = Http::get($url, [
                'client' => 'gtx',
                'sl'     => $sourceLangCode,
                'tl'     => $targetLangCode,
                'dt'     => 't',
                'q'      => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[0]) && is_array($data[0])) {
                    $translatedParts = array_map(function ($part) {
                        return $part[0] ?? '';
                    }, $data[0]);
                    
                    $translatedText = implode('', $translatedParts);
                    return response()->json(['translatedText' => $translatedText]);
                }
            }

            // Fallback to original text if translation fails
            return response()->json(['translatedText' => $text]);
        } catch (\Exception $e) {
            Log::warning('Google Translate proxy error: ' . $e->getMessage());
            return response()->json(['translatedText' => $request->text ?? '']);
        }
    }

    /**
     * Map language name to ISO 639-1 language code.
     */
    private function resolveLanguageCode(string $lang): string
    {
        $langLower = strtolower(trim($lang));
        
        $mapping = [
            'english'   => 'en',
            'hindi'     => 'hi',
            'gujarati'  => 'gu',
            'marathi'   => 'mr',
            'tamil'     => 'ta',
            'telugu'    => 'te',
            'kannada'   => 'kn',
            'malayalam' => 'ml',
            'bengali'   => 'bn',
            'punjabi'   => 'pa',
            'urdu'      => 'ur',
            'auto'      => 'auto',
        ];

        return $mapping[$langLower] ?? $lang;
    }
}
