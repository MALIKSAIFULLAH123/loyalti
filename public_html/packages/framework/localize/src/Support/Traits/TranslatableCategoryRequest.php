<?php

namespace MetaFox\Localize\Support\Traits;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;

trait TranslatableCategoryRequest
{
    protected $categoryNameKey = 'name';

    /**
     * @return array<string, mixed>
     */
    public function getCategoryNameRule(bool $isEdit = false): array
    {
        return [
            $this->categoryNameKey => [$isEdit ? 'sometimes' : 'required', 'array', new TranslatableTextRule()],
        ];
    }

    public function extractCategoryNameData(array $data): array
    {
        Arr::set($data, $this->categoryNameKey, Language::extractPhraseData($this->categoryNameKey, $data));

        return $data;
    }
}
