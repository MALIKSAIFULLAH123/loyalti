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
class UserActivityItem extends JsonResource
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
            'label'       => 'Blog',
            'value'       => 0,
            'icon_name'   => 'newspaper-alt',
            'icon_family' => 'Lineficon',
            'icon_color'  => '#0097fc',
        ];
    }
}
