<?php

namespace MetaFox\Page\Http\Resources\v1\CustomField\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Models\Field as Model;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DuplicateFieldForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DuplicateFieldForm extends CreateFieldForm
{
    protected function getValues(): array
    {
        $extra            = $this->resource->extra;
        $validationFields = Arr::get($extra, 'validation', []);
        $values           = [
            'type_id'         => $this->resource->type_id,
            'is_active'       => $this->resource->is_active,
            'field_name'      => $this->resource->field_name,
            'section_id'      => $this->resource->section_id,
            'var_type'        => $this->resource->var_type,
            'view_type'       => $this->resource->view_type,
            'edit_type'       => $this->resource->edit_type,
            'is_required'     => $this->resource->is_required,
            'label'           => $this->getPhraseValues(sprintf('profile::phrase.%s_label', $this->resource->field_name)),
            'description'     => $this->getPhraseValues(sprintf('profile::phrase.%s_description', $this->resource->field_name)),
            'has_label'       => $this->resource->has_label,
            'has_description' => $this->resource->has_description,
            'is_feed'         => $this->resource->is_feed,
            'is_search'       => $this->resource->is_search,
            'is_register'     => $this->resource->is_register,
        ];

        $values = array_merge($values, $validationFields, $this->getValidationFieldsValues($validationFields));

        if (count($this->resource->options)) {
            Arr::set($values, 'options', $this->getOptionsFieldForDuplicate());
        }

        return $values;
    }

    public function getUserType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_PAGE;
    }

    public function getActiveField(): ?AbstractField
    {
        return Builder::checkbox('is_active')
            ->label(__p('core::phrase.is_active'));
    }
}
