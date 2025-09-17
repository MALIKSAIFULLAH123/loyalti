<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

use MetaFox\Form\Html\BuiltinAdminSearchForm;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchCountryForm.
 */
class SearchCountryForm extends BuiltinAdminSearchForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.search'))
            ->acceptPageParams(['q'])
            ->action(apiUrl('admin.localize.country.index'));
    }
}
