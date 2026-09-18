<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Language;
use App\Models\LanguageTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RegenerateLanguageTranslations extends Command
{
    protected $signature = 'language:regenerate {iso_code}';
    protected $description = 'Regenerate translations for a specific language';

    public function handle()
    {
        $isoCode = $this->argument('iso_code');
        
        $language = Language::where('iso_code', $isoCode)->first();
        if (!$language) {
            $this->error("Language with ISO code '{$isoCode}' not found!");
            return 1;
        }

        $this->info("Regenerating translations for: {$language->name} ({$isoCode})");
        
        // 1. Delete existing translations
        $deleted = LanguageTranslation::where('language_id', $language->id)->delete();
        $this->info("Deleted {$deleted} existing translations");
        
        // 2. Load keys from vn.json
        $vnJsonPath = resource_path('lang/vn.json');
        if (!file_exists($vnJsonPath)) {
            $this->error('vn.json not found!');
            return 1;
        }
        
        $vnData = json_decode(file_get_contents($vnJsonPath), true);
        $keys = array_keys($vnData);
        $totalKeys = count($keys);
        
        $this->info("Found {$totalKeys} keys to translate");
        
        $targetMap = ['vn' => 'vi', 'kr' => 'ko'];
        $targetApi = $targetMap[$isoCode] ?? $isoCode;
        
        // 3. Handle English with en.json
        if ($isoCode === 'en') {
            $enJsonPath = resource_path('lang/en.json');
            if (file_exists($enJsonPath)) {
                $enData = json_decode(file_get_contents($enJsonPath), true);
                $missingKeys = [];
                
                $this->info("Using en.json for existing translations...");
                $bar = $this->output->createProgressBar($totalKeys);
                
                foreach ($keys as $key) {
                    if (isset($enData[$key])) {
                        LanguageTranslation::create([
                            'language_id' => $language->id,
                            'key' => $key,
                            'value' => $enData[$key]
                        ]);
                    } else {
                        $missingKeys[] = $key;
                    }
                    $bar->advance();
                }
                $bar->finish();
                $this->newLine();
                
                if (!empty($missingKeys)) {
                    $this->info("Auto-translating " . count($missingKeys) . " missing keys...");
                    $bar = $this->output->createProgressBar(count($missingKeys));
                    
                    foreach ($missingKeys as $key) {
                        $translated = $this->translateText($key, 'en');
                        LanguageTranslation::create([
                            'language_id' => $language->id,
                            'key' => $key,
                            'value' => $translated
                        ]);
                        $bar->advance();
                    }
                    $bar->finish();
                    $this->newLine();
                }
                
                Cache::flush();
                $this->info("✓ Successfully created {$totalKeys} translations for English");
                return 0;
            }
        }
        
        // 4. For Vietnamese, just copy keys
        if ($isoCode === 'vi' || $isoCode === 'vn') {
            $bar = $this->output->createProgressBar($totalKeys);
            foreach ($keys as $key) {
                LanguageTranslation::create([
                    'language_id' => $language->id,
                    'key' => $key,
                    'value' => $key
                ]);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info("✓ Successfully copied {$totalKeys} keys for Vietnamese");
            Cache::flush();
            return 0;
        }
        
        // 5. For other languages, auto-translate
        $this->info("Auto-translating to {$isoCode}...");
        $bar = $this->output->createProgressBar($totalKeys);
        
        foreach ($keys as $key) {
            $translated = $this->translateText($key, $targetApi);
            LanguageTranslation::create([
                'language_id' => $language->id,
                'key' => $key,
                'value' => $translated
            ]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        Cache::flush();
        $this->info("✓ Successfully created {$totalKeys} translations");
        
        return 0;
    }
    
    private function translateText($text, $targetLang)
    {
        try {
            $response = Http::get("https://translate.googleapis.com/translate_a/single", [
                'client' => 'gtx',
                'sl' => 'vi',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text,
            ]);
            
            if ($response->successful()) {
                return $response->json()[0][0][0] ?? $text;
            }
        } catch (\Exception $e) {
            // Return original text on error
        }
        
        return $text;
    }
}
