<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

/**
 * --------------------------------------------------------------------------
 * EditForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditCountryForm.
 */
class UpdateCountryForm extends StoreCountryForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('localize::country.edit_country'))
            ->action('/admincp/localize/country/' . $this->resource->id)
            ->asPut()
            ->setValue($this->resource->toArray());
    }
}
