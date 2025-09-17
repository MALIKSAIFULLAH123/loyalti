<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryChild\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Models\CountryChild as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchCountryChildForm.
 * @property Model $resource
 */
class SearchCountryChildForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.search'))
            ->action('/country-child');
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['variant' => 'horizontal']);
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::submit()
                ->forAdminSearchForm()
        );
    }
}
