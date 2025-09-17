<?php

namespace MetaFox\GoogleTranslate\Support;

use Exception;
use Google\Cloud\Translate\V2\TranslateClient;
use MetaFox\Translation\Models\TranslationGateway;
use MetaFox\Translation\Support\AbstractTranslationGateway;

class GoogleTranslate extends AbstractTranslationGateway
{
    public function __construct(TranslationGateway $gateway)
    {
        parent::__construct($gateway);
    }

    public function translate(string $text, array $attributes): array
    {
        $targetLang = $attributes['target'];

        // Google Translate API call
        $config = $this->getTranslationGateway()->config;

        if (empty($config['api_key'])) {
            abort(403, __p('googletranslate::phrase.google_api_key_is_missing'));
        }

        try {
            $translate = new TranslateClient([
                'key' => $config['api_key'],
                'restOptions' => [
                    'headers' => [
                        'referer' => config('app.url'),
                    ]
                ]
            ]);
            $result = $translate->translate($text, [
                'target' => $targetLang
            ]);
        } catch (Exception $e) {
            // Handle the exception here
            // You can log the error or return a default translation
            $apiError = json_decode($e->getMessage(), true);
            abort($e->getCode(), $apiError['error']['message'] ?? $e->getMessage());
        }

        return [
            'origin_text'       => $text,
            'translated_text'   => $result['text'],
            'target'            => $targetLang,
        ];
    }

    public function isAvailable(): bool
    {
        $config = $this->getTranslationGateway()->config;
        if (empty($config['api_key'])) {
            return false;
        }
        return true;
    }
}
