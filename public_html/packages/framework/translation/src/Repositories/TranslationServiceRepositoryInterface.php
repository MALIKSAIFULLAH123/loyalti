<?php

namespace MetaFox\Translation\Repositories;

interface TranslationServiceRepositoryInterface
{
    /**
     * @param string $text
     * @param array  $attributes
     *
     * @return array
     */
    public function translate(string $text, array $attributes = []): array|null;

    public function checkConfigTranslation(): bool;
}
