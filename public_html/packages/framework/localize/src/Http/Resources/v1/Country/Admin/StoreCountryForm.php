<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * EditForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreCountryForm.
 */
class StoreCountryForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('localize::country.add_country'))
            ->asPost()
            ->action('/admincp/localize/country');
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('country_iso')
                ->required()
                ->label(__p('localize::phrase.iso'))
                ->maxLength(2)
                ->placeholder(__p('localize::phrase.iso')),
            Builder::text('name')
                ->required()
                ->label(__p('localize::country.name_label'))
                ->placeholder(__p('localize::country.fill_country_name')),
        );

        $this->addDefaultFooter(true);
    }
}
