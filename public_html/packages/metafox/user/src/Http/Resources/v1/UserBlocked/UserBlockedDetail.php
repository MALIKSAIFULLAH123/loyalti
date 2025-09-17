<?php

namespace MetaFox\User\Http\Resources\v1\UserBlocked;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserEntity as Model;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class UserBlockedDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserBlockedDetail extends JsonResource
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
            'module_name'   => 'user',
            'resource_name' => 'blocked_user',
            'user_name'     => $this->resource->user_name,
            'full_name'     => $this->resource->name,
            'avatar'        => $this->resource->avatars,
            'is_featured'   => false,
            'is_blocked'    => true,
        ];
    }
}
