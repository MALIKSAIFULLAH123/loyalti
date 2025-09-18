<?php

namespace MetaFox\Like\Http\Resources\v1\Like;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Like\Models\Like as Model;
use MetaFox\Like\Support\Facades\MobileAppAdapter;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class LikeItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LikeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        /**@deprecated v1.9 remove "transformLegacyData" and response "$this->resource->reaction->entityId()" */
        $id = MobileAppAdapter::transformLegacyData($this->resource->reaction->entityId(), 'v1.8');
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'preaction',
            'resource_name' => $this->resource->entityType(),
            'is_owner'      => $this->resource->userId() == user()->entityId(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'react_id'      => $id,
        ];
    }
}
