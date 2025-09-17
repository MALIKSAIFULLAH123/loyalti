<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Profile\Models\Field as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditFieldForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditFieldForm extends CreateFieldForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.profile.field.update', [
                'field' => $this->resource?->id,
            ]))
            ->asPut()
            ->setValue($this->getValues());
    }

    protected function getValues(): array
    {
        $values = parent::getValues();

        Arr::set($values, 'is_feed', $this->resource->is_feed);
        Arr::set($values, 'is_search', $this->resource->is_search);
        Arr::set($values, 'is_register', $this->resource->is_register);
        Arr::set($values, 'roles', $this->resource->roles->pluck('id')->toArray());
        Arr::set($values, 'visible_roles', $this->resource->visibleRoles->pluck('id')->toArray());

        return $values;
    }

    public function getActiveField(): ?AbstractField
    {
        return null;
    }

    public function isEdit(): bool
    {
        return true;
    }

    public function getRegisterField(): ?AbstractField
    {
        return null;
    }
}
