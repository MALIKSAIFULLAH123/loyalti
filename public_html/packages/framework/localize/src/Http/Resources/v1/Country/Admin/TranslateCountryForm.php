<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class TranslateCountryForm.
 * @protected Model $resource
 */
class TranslateCountryForm extends AbstractForm
{
    /** @var bool */
    protected bool $isEdit = true;

    protected function prepare(): void
    {
        $this
            ->title(__p('localize::country.translate_country'))
            ->action('/admincp/country/' . $this->resource->id)
            ->asPut()
            ->setValue([
                'text.en' => $this->resource->name,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('text.en')
                ->required()
                ->label('en')
                ->placeholder('en name'),
        );

        $this->addDefaultFooter();
    }
}
