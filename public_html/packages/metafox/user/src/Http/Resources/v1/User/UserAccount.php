<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\User as Model;

/**
 * Class UserDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserAccount extends JsonResource
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
        return [
            'id'               => $this->resource->entityId(),
            'user_name'        => $this->resource->user_name,
            'full_name'        => $this->resource->full_name,
            'email'            => $this->resource->email,
            'language_id'      => 0, // @todo how to get language_id, timezone, default_currency
            'timezone'         => 'z138',
            'default_currency' => 'USD',
        ];
    }
}
