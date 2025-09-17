<?php

namespace MetaFox\Featured\Http\Resources\v1\Package\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Featured\Models\Package as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class EditPackageForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditPackageForm extends CreatePackageForm
{
    protected function prepare(): void
    {
        $itemTypes = $this->resource->item_types->pluck('item_type')->toArray();

        $roleIds   = $this->resource->role_ids->pluck('role_id')->toArray();

        $this->title(__p('featured::admin.edit_package'))
            ->action('admincp/featured/package/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'title' => $this->resource->title,
                'is_free' => (int) $this->resource->is_free,
                'is_forever_duration' => (int) $this->resource->is_forever_duration,
                'price' => $this->resource->price,
                'duration_period' => $this->resource->duration_period,
                'duration_value' => $this->resource->duration_value,
                'applicable_item_types' => count($itemTypes) > 0 ? $itemTypes : null,
                'applicable_role_ids' => count($roleIds) > 0 ? $roleIds : null,
                'is_active' => $this->resource->is_active,
            ]);
    }

    protected function isEdit(): bool
    {
        return true;
    }
}
