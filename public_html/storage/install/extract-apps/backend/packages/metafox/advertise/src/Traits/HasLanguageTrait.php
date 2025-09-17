<?php

namespace MetaFox\Advertise\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support as Facade;

/**
 * @property Model $resource
 */
trait HasLanguageTrait
{
    public function getLanguageOptions(): array
    {
        return Facade::getLanguageOptions();
    }

    public function getEditLanguages(): ?array
    {
        $ids            = $this->resource->languages()->allRelatedIds()->toArray();
        $languageActive = Arr::pluck($this->getLanguageOptions(), 'value');

        $ids = array_filter($ids, function ($id) use ($languageActive) {
            return in_array($id, $languageActive);
        });

        if (count($ids)) {
            return $ids;
        }

        return null;
    }
}
