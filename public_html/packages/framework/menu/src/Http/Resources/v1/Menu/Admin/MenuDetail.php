<?php

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Menu\Models\Menu as Model;

/**
 * |--------------------------------------------------------------------------
 * | Resource Detail
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/detail.stub
 * | @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview.
 **/

/**
 * Class MenuDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MenuDetail extends JsonResource
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
        return [
            'id'        => $this->resource->id,
            'name'      => $this->resource->name,
            'module_id' => $this->resource->module_id,
        ];
    }
}
