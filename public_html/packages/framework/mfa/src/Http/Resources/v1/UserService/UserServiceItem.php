<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Mfa\Models\UserService as Model;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class UserServiceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class UserServiceItem extends JsonResource
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
            'id'         => $this->resource->entityId(),
            'user_id'    => $this->resource->user_id,
            'user_type'  => $this->resource->user_type,
            'service'    => $this->resource->service,
            'is_active'  => $this->resource->is_active,
            'created_at' => $this->resource->created_at,
        ];
    }
}
