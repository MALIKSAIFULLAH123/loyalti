<?php

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserVerify as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class UserVerifyDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class UserVerifyDetail extends JsonResource
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
            'id'            => $this->id,
            'module_name'   => '',
            'resource_name' => $this->entityType(),
        ];
    }
}
