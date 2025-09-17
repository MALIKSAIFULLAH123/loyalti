<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Friend\Models\FriendList as Model;

/**
 * Class FriendListDetail.
 * @property Model $resource
 */
class FriendListDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => 'friend',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'total_friend'  => $this->resource->userEntities->count(),
        ];
    }
}
