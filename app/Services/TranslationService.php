<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate text using translate.googleapis.com
     * Returns translated string on success, or original text on failure.
     */
    /**
     * Translate a batch of texts.
     * Returns an array mapping original text => translated text.
     */
    public static function translateBatch($texts, $targetLang)
    {
        $results = [];
        foreach ($texts as $text) {
            // 1. Protect Placeholders
            list($protectedText, $placeholders) = self::protectPlaceholders($text);

            // 2. Translate with Retry
            $translatedProtected = self::translateWithRetry($protectedText, $targetLang);

            // 3. Restore Placeholders
            $finalText = self::restorePlaceholders($translatedProtected, $placeholders);
            
            $results[$text] = $finalText;
        }
        return $results;
    }

    private static function translateWithRetry($text, $targetLang, $retries = 3)
    {
        $attempt = 0;
        while ($attempt < $retries) {
            try {
                // Use user-provided translation API
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])->timeout(15)->get('https://api.cmsnt.co/translation-api.php', [
                    'q' => $text,
                    'target' => $targetLang,
                ]);

                if ($response->successful()) {
                    $contentType = $response->header('Content-Type', '');
                    $body = (string) $response->body();

                    if (str_contains($contentType, 'application/json')) {
                        $json = $response->json();
                        if (is_array($json)) {
                            if (isset($json['data']['translations'][0]['translatedText'])) {
                                return $json['data']['translations'][0]['translatedText'];
                            }
                            if (isset($json['translated'])) {
                                return $json['translated'];
                            }
                            if (isset($json[0])) {
                                return is_string($json[0]) ? $json[0] : $body;
                            }
                        }
                    }

                    if (!empty($body)) {
                        return $body;
                    }
                }
                
                // If 429, wait longer
                if ($response->status() === 429) {
                    sleep(2);
                }
            } catch (\Exception $e) {
                Log::warning('TranslationService attempt ' . ($attempt + 1) . ' failed: ' . $e->getMessage());
            }
            $attempt++;
            usleep(500000); // 500ms backoff
        }
        return $text; // Fallback to original
    }

    /**
     * Protects variables like :name, {amount}, %s, {{var}}, <b>...</b>
     */
    private static function protectPlaceholders($text)
    {
        $placeholders = [];
        $pattern = '/(\{[^\}]+\}|:\w+|%s|%\d+\$\w|\{\{.*?\}\}|<[^>]+>)/';
        
        $protected = preg_replace_callback($pattern, function ($matches) use (&$placeholders) {
            $token = '__PH_' . count($placeholders) . '__';
            $placeholders[] = $matches[0];
            return $token;
        }, $text);

        return [$protected, $placeholders];
    }

    private static function restorePlaceholders($text, $placeholders)
    {
        foreach ($placeholders as $index => $original) {
            $token = '__PH_' . $index . '__';
            $text = str_replace($token, $original, $text);
            // Also handle case where translator adds spaces e.g. __PH_ 0 __
            $text = preg_replace('/__PH_\s*' . $index . '\s*__/', $original, $text);
        }
        return $text;
    }

    /**
     * Legacy wrapper for single text
     */
    public static function translate($text, $targetLang)
    {
        $batch = self::translateBatch([$text], $targetLang);
        return $batch[$text] ?? $text;
    }
}
