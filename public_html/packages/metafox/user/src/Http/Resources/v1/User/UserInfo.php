<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Traits\UserLocationTrait;

/**
 * |--------------------------------------------------------------------------
 * | Resource
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/base.stub
 * | @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview.
 **/

/**
 * Class User.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserInfo extends JsonResource
{
    use UserLocationTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function toArray($request): array
    {
        $data = [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'user',
            'resource_name' => $this->resource->entityType(),
        ];

        $data['sections'] = CustomProfile::getProfileValues($this->resource, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'for_form'     => false,
        ]);

        return $data;
    }
}
