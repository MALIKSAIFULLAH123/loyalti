<?php

namespace MetaFox\Group\Http\Resources\v1\CustomField\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Traits\CreateFieldFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateFieldForm
 * @ignore
 * @codeCoverageIgnore
 */
class CreateFieldForm extends AbstractForm
{
    use CreateFieldFormTrait;

    protected function getValues(): array
    {
        return [
            'type_id'         => 'main',
            'edit_type'       => CustomFieldSupport::TEXT,
            'view_type'       => 'text',
            'var_type'        => CustomFieldSupport::TYPE_STRING,
            'has_label'       => 1,
            'has_description' => 1,
            'is_active'       => 1,
        ];
    }

    public function getUserType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_GROUP;
    }

    public function getActiveField(): ?AbstractField
    {
        return Builder::checkbox('is_active')
            ->label(__p('core::phrase.is_active'));
    }

    public function getSearchField(): ?AbstractField
    {
        return Builder::checkbox('is_search')
            ->label(__p('group::phrase.include_on_search_group'));
    }

    public function getHasDescriptionField(): ?AbstractField
    {
        if ($this->getSearchField() == null) {
            return null;
        }

        return Builder::checkbox('has_description')
            ->showWhen(['truthy', 'is_search'])
            ->label(__p('group::phrase.include_the_field_description_in_the_search_form'));
    }
}
