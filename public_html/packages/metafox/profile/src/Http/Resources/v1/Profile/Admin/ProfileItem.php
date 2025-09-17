<?php

namespace MetaFox\Profile\Http\Resources\v1\Profile\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Profile\Models\Profile as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * class ProfileItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ProfileItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'profile_type' => $this->profile_type,
            'title'        => $this->resource->title,
            'description'  => $this->resource->description,
            'links'        => [
                'editItem'  => '/profile/profile/edit/' . $this->id,
                'structure' => '/profile/profile/' . $this->id . '/structure/browse',
            ],
        ];
    }
}
