<?php

namespace MetaFox\Group\Http\Resources\v1\CustomField\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Traits\CreateFieldFormTrait;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class EditFieldForm
 * @ignore
 * @codeCoverageIgnore
 */
class EditFieldForm extends AbstractForm
{
    use CreateFieldFormTrait;

    protected function prepare(): void
    {
        $values = $this->getValues();

        Arr::set($values, 'is_search', $this->resource->is_search);

        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.profile.field.update', [
                'field' => $this->resource?->id,
            ]))
            ->asPut()
            ->setValue($values);
    }

    public function getUserType(): string
    {
        return CustomField::SECTION_TYPE_GROUP;
    }

    public function isEdit(): bool
    {
        return true;
    }

    public function getActiveField(): ?AbstractField
    {
        return null;
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
