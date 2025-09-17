<?php

namespace MetaFox\Localize\Contracts;

use MetaFox\Localize\Models\Language as Model;

/**
 * Interface LanguageSupportContract.
 */
interface LanguageSupportContract
{
    /**
     * @return array<string, Model>
     */
    public function getLanguages(): array;

    /**
     * @param string $languageId
     *
     * @return Model|null
     */
    public function getLanguage(string $languageId): ?Model;

    /**
     * @return array<string, Model>
     */
    public function getAllActiveLanguages(): array;

    /**
     * @return array<array<string, mixed>>
     */
    public function getActiveOptions(): array;

    /**
     * @param string|null $code
     *
     * @return string|null
     */
    public function getName(?string $code): ?string;

    /**
     * @return array<string>
     */
    public function availableLocales(): array;

    /**
     * @return array<string>
     */
    public function getAllLocales(): array;

    /**
     * @param  string                           $key
     * @param  array<string, mixed>             $data
     * @return array<int, array<string, mixed>>
     */
    public function extractPhraseData(string $key, array $data = []): array;

    /**
     * @param  string               $phraseKey
     * @return array<string, mixed>
     */
    public function getPhraseValues(string $phraseKey): array;

    /**
     * @return array<string, string>
     */
    public function getEmptyPhraseData(): array;

    /**
     * Enable the display translation key setting.
     *
     * @return void
     */
    public function enableEditMode(): void;

    /**
     * Disable the display translation key setting.
     *
     * @return void
     */
    public function disableEditMode(): void;
}
