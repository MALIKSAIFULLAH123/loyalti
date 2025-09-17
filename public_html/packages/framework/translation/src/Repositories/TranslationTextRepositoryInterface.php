<?php

namespace MetaFox\Translation\Repositories;

use MetaFox\Translation\Models\TranslationText;

interface TranslationTextRepositoryInterface
{
    public function getTranslatedText(array $attributes);

    public function createTranslationText(array $attributes): TranslationText;

    public function deleteByItem(int $entityId, string $entityType);
}
