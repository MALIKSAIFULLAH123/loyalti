<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Http\Resources\v1\User\UserItem;
use MetaFox\User\Models\User as Model;

/**
 * Class FriendItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FriendItem extends JsonResource
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
        return (new UserItem($this->resource))->toArray($request);
    }
}
