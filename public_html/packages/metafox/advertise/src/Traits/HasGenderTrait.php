<?php

namespace MetaFox\Advertise\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support as Facade;

/**
 * @property Model $resource
 */
trait HasGenderTrait
{
    public function getGenderOptions(): array
    {
        return Facade::getGenderOptions();
    }

    public function getEditGenders(): ?array
    {
        $ids          = $this->resource->genders()->allRelatedIds()->toArray();
        $genderActive = Arr::pluck($this->getGenderOptions(), 'value');

        $ids = array_filter($ids, function ($id) use ($genderActive) {
            return in_array($id, $genderActive);
        });

        if (count($ids)) {
            return $ids;
        }

        return null;
    }
}
