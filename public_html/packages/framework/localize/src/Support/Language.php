<?php

namespace MetaFox\Localize\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use MetaFox\Core\Support\CacheManager;
use MetaFox\Localize\Contracts\LanguageSupportContract;
use MetaFox\Localize\Models\Language as Model;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

class Language implements LanguageSupportContract
{
    /**
     * @var array<string, Model>
     */
    private array $languages;

    public function __construct()
    {
        $this->init();
    }

    public function getCacheName(): string
    {
        return CacheManager::CORE_LANGUAGE_CACHE;
    }

    public function clearCache(): void
    {
        Cache::forget($this->getCacheName());
    }

    public function getLanguage(string $languageId): ?Model
    {
        return $this->languages[$languageId] ?? null;
    }

    /**
     * @return array<string, Model>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getAllActiveLanguages(): array
    {
        return Arr::where($this->languages, function (Model $value) {
            return $value->is_active;
        });
    }

    public function getDefaultLocaleId(): string
    {
        return Settings::get('localize.default_locale', 'en');
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getActiveOptions(): array
    {
        $localeDefault = Settings::get('localize.default_locale', 'en');
        return Cache::rememberForever('Language__activeOptions', function () use ($localeDefault) {
            return array_map(function (Model $item) {
                return ['value' => $item->language_code, 'label' => $item->name];
            }, Model::query()
                ->where('is_active', 1)
                ->orderByRaw("CASE WHEN language_code = '$localeDefault' THEN 1 ELSE 0 END DESC")
                ->get()
                ->all());
        });
    }

    public function getName(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        return Cache::rememberForever('language_' . $code, function () use ($code) {
            /** @var ?Model $model */
            $model = Model::query()->where('language_code', '=', $code)->first();

            return $model?->name;
        });
    }

    protected function init(): void
    {
        $localeDefault   = Settings::get('localize.default_locale', 'en');
        $this->languages = Cache::remember(
            $this->getCacheName(),
            3000,
            function () use ($localeDefault) {
                return Model::query()
                    ->orderByRaw("CASE WHEN language_code = '$localeDefault' THEN 1 ELSE 0 END DESC")
                    ->orderBy('id')
                    ->get()
                    ->keyBy('language_code')
                    ->all();
            }
        );
    }

    /**
     * @return array<string>
     */
    public function availableLocales(): array
    {
        return Cache::rememberForever('language_supported_locales', function () {
            $locale = Model::query()->where('is_active', '=', 1)
                ->get()
                ->pluck('language_code')
                ->toArray();

            if (!is_array($locale)) {
                return [];
            }

            return $locale;
        });
    }

    /**
     * @return array<string>
     */
    public function getAllLocales(): array
    {
        return Cache::rememberForever('language_all_locales', function () {
            $locale = Model::query()
                ->get()
                ->pluck('language_code')
                ->toArray();

            if (!is_array($locale)) {
                return [];
            }

            return $locale;
        });
    }

    /**
     * @inheritDoc
     */
    public function extractPhraseData(string $key, array $data = []): array
    {
        $phraseRawData = Arr::get($data, $key, []);

        if (!is_array($phraseRawData)) {
            return [];
        }

        $defaultLocale = $this->getDefaultLocaleId();

        $locales       = $this->getAllLocales();
        $phraseRawData = Arr::only($phraseRawData, $locales);
        $defaultText   = Arr::get($phraseRawData, $defaultLocale) ?: '';

        foreach ($locales as $locale) {
            $phraseRawData = Arr::add($phraseRawData, $locale, $defaultText);
        }

        return Arr::only($phraseRawData, $locales);
    }

    /**
     * @inheritDoc
     */
    public function getPhraseValues(string $phraseKey): array
    {
        $availableLocales = array_keys($this->getAllActiveLanguages());
        $values           = [];

        foreach ($availableLocales as $locale) {
            $value = __p($phraseKey, [], $locale);

            if (config('localize.disable_translation')) {
                $value = preg_replace('#^\[(.*)\]$#ms', '$1', $value);
            }

            $values[$locale] = $value;
        }

        return Arr::undot($values);
    }

    /**
     * @return array<string, string>
     */
    public function getEmptyPhraseData(): array
    {
        $data    = [];
        $locales = $this->getAllLocales();

        foreach ($locales as $locale) {
            $data[$locale] = MetaFoxConstant::EMPTY_STRING;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function enableEditMode(): void
    {
        Config::set('localize.display_translation_key', true);
        Config::set('localize.view_mode', 'edit');
    }

    /**
     * @inheritDoc
     */
    public function disableEditMode(): void
    {
        Config::set('localize.display_translation_key', false);
        Config::set('localize.view_mode', 'view');
    }
}
