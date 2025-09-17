<?php

namespace MetaFox\Search\Http\Resources\v1\Type\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Search\Models\Type as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSearchTypeForm.
 * @property Model $resource
 */
class EditSearchTypeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('admincp/search/type/' . $this->resource->id)
            ->asPost()
            ->setValue(new TypeItem($this->resource));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->label('Title')
                ->placeholder('Fill in a title'),
            Builder::text('title')
                ->label('Type')
                ->readOnly(true)
                ->description('readOnly value'),
            Builder::checkbox('is_active')
                ->label(__p('search::phrase.enable_this_search_type')),
            Builder::checkbox('is_system')
                ->label(__p('search::phrase.is_system_search_type')),
            Builder::checkbox('can_search_feed')
                ->label(__p('search::phrase.search_type_can_search_feed')),
        );

        $this->addDefaultFooter(true);
    }
}
