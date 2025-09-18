<?php

namespace MetaFox\Group\Http\Resources\v1\Rule;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Group\Models\Rule as Model;
use MetaFox\Group\Support\Facades\GroupRule;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateGroupRuleForm.
 * @property Model $resource
 */
class UpdateGroupRuleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action(url_utility()->makeApiResourceUrl('group-rule', $this->resource->entityId()))
            ->asPut()
            ->secondAction('@updatedItem/group_rule')
            ->title(__p('group::phrase.edit_rule'))
            ->setValue([
                'title'       => $this->resource->title,
                'description' => $this->resource->description,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->minLength(MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH)
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.fill_in_a_title')),
            Builder::textArea('description')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('core::phrase.fill_in_a_description'))
                ->maxLength(GroupRule::getDescriptionMaxLength()),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__('core::phrase.save_changes'))->sizeSmall(),
                Builder::cancelButton()->sizeSmall(),
            );
    }
}
