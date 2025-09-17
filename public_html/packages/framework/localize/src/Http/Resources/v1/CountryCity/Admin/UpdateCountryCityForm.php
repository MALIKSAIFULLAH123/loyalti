<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryCity\Admin;

use MetaFox\Localize\Models\CountryCity as Model;

/**
 * Class StoreCountryCityForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCountryCityForm extends StoreCountryCityForm
{
    protected bool $isEdit = true;

    protected function prepare(): void
    {
        $this->title(__p('localize::phrase.edit_city'))
            ->action(apiUrl('admin.localize.country.city.update', ['city' => $this->resource?->entityId()]))
            ->asPut()
            ->setValue([
                'name'       => $this->resource?->name,
                'city_code'  => $this->resource?->city_code,
                'state_code' => $this->resource?->state_code ?: 0,
            ]);
    }
}
