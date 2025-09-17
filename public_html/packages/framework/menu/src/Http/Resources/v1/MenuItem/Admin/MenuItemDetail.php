<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Menu\Models\MenuItem as Model;

/**
 * |--------------------------------------------------------------------------
 * | Resource Detail
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/detail.stub
 * | @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview.
 **/

/**
 * Class MenuItemDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MenuItemDetail extends JsonResource
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
        $obj = $this->resource;

        return [
            'id'          => $obj->id,
            'name'        => $obj->name,
            'parent_name' => $obj->parent_name,
            'label'       => $obj->label,
            'module_id'   => $obj->module_id,
            'is_active'   => $obj->is_active,
            'icon'        => $obj->icon,
            'extra'       => $this->getExtra($this->resource),
        ];
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
