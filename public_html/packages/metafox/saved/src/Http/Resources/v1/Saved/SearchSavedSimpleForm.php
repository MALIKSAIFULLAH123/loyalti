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
 * Class SearchSavedForm.
 * @property ?Model $resource
 */
class SearchSavedSimpleForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->action('/saved/search');
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::searchBox('q')
                    ->placeholder(__p('saved::phrase.search_saved_items'))
                    ->className('mb2')
                    ->marginNone()
                    ->sx([
                        'paddingBottom' => 1
                    ]),
                Builder::choice('type')
                    ->disableClearable()
                    ->label(__p('core::phrase.select_type'))
                    ->options(SavedType::getFilterOptions())
                    ->defaultValue('all'),
            );
    }
}
