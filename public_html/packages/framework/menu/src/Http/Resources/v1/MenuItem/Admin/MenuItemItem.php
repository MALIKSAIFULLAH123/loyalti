<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\App\Models\Package;
use MetaFox\Menu\Models\MenuItem as Model;

/**
 * Class MenuItemItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MenuItemItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $obj           = $this->resource;
        $hasChild      = $this->resource->child_count > 1; //There will be always at least 1 item => starting from 1
        $packageId     = $this->resource->package_id ?? 'metafox/core';
        $childrenUrl   = $hasChild ? $this->getChildrenItemLink() : null;

        $package       = null;
        try {
            $package  = app('core.packages')->getPackageByName($packageId);
        } catch (\Throwable) {
            // Just silent the error package not found
        }

        return [
            'id'        => $obj->id,
            'name'      => $obj->name,
            'label'     => $obj->label ?: '',
            'module_id' => $package instanceof Package ? $package?->title : __p('core::phrase.unknown'),
            'is_active' => $obj->is_active,
            'icon'      => $obj->icon ? $obj->icon : 'ico-minus',
            'iconColor' => $obj->icon_color,
            'testid'    => $obj->name,
            'has_child' => $hasChild,
            'url'       => $package instanceof Package ? $childrenUrl : null,
            'extra'     => $this->getExtra($this->resource),
        ];
    }

    protected function getChildrenItemLink(): string
    {
        return sprintf('/menu/menu_item/%s/child/browse', $this->resource->entityId());
    }

    /**
     * @param  Model                $resource
     * @return array<string, mixed>
     */
    protected function getExtra(Model $resource): array
    {
        return [
            'can_delete' => $resource->is_custom,
        ];
    }
}
