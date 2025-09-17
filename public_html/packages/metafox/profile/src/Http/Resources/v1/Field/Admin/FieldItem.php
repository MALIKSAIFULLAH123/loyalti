<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Profile\Models\Field as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * class FieldItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class FieldItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $roleText     = $visibleRolesText = __p('core::phrase.all_roles');
        $roles        = collect($this->resource->roles);
        $visibleRoles = $this->resource->visibleRoles;
        $isRegister   = $this->is_register;

        if ($roles->isNotEmpty()) {
            $roleText = $roles->pluck('name')->implode(', ');
        }

        if ($visibleRoles->isNotEmpty()) {
            $visibleRolesText = $visibleRoles->pluck('name')->implode(', ');
        }

        return [
            'id'               => $this->id,
            'resource_name'    => $this->resource->entityType(),
            'field_name'       => $this->field_name,
            'type_id'          => $this->type_id,
            'is_active'        => $this->is_active,
            'section_id'       => $this->section_id,
            'group'            => $this->resource->section?->label,
            'description'      => $this->editingDescription,
            'var_type'         => $this->var_type,
            'view_type'        => $this->view_type,
            'edit_type'        => $this->edit_type,
            'is_register'      => $isRegister,
            'is_required'      => $this->is_required,
            'ordering'         => $this->ordering,
            'options'          => $this->options,
            'is_search'        => $this->is_search,
            'is_feed'          => $this->is_feed,
            'label'            => $this->resource->editingLabel,
            'has_label'        => $this->resource->has_label,
            'has_description'  => $this->resource->has_description,
            'section_type'     => $this->resource->section->getUserType(),
            'roles'            => $roleText,
            'visible_roles'    => $visibleRolesText,
            'extra'            => $this->extra,
            'disable_register' => Arr::get($this->extra, 'disable_register', false),
            'links'            => [
                'editItem'      => $this->resource->admin_edit_url,
                'duplicateItem' => url_utility()->makeApiUrl('profile/field/duplicate/' . $this->resource->entityId()),
            ],
        ];
    }
}
