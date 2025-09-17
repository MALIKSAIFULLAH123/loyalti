<?php

namespace MetaFox\Saved\Http\Resources\v1\Saved;

use MetaFox\Form\Builder;
use MetaFox\Form\Html\BuiltinSearchForm;
use MetaFox\Saved\Models\Saved as Model;
use MetaFox\Saved\Support\Facade\SavedType;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SideBarFilterForm.
 * @property ?Model $resource
 */
class SideBarFilterForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->action('/saved/search')
            ->setValue(['type' => 'all'])
            ->acceptPageParams(['type']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('type')
                ->label(__p('core::phrase.select_type'))
                ->options(SavedType::getFilterOptions()),
        );
    }
}
