<?php

namespace MetaFox\User\Http\Resources\v1\UserShortcut;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserEntity as Model;

/**
 * |--------------------------------------------------------------------------
 * | Resource Detail
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/detail.stub
 * | @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview.
 **/

/**
 * Class UserShortcutDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserShortcutDetail extends JsonResource
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
            'id'            => $this->resource->id,
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'full_name'     => $this->resource->name,
            'user_name'     => $this->resource->user_name,
            'avatar'        => $this->resource->avatars,
            'is_featured'   => $this->resource->is_featured,
            'short_name'    => $this->resource->short_name,
            'sort_type'     => $this->resource->sort_type,
        ];
    }
}
