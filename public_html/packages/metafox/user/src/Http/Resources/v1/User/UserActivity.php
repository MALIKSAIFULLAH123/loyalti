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
class UserActivity extends JsonResource
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
            'id'           => $this->resource->entityId(),
            'total_items'  => [
                'label' => 'Total items',
                'value' => 0,
            ],
            'total_points' => [
                'label' => 'Activity Points',
                'value' => 0,
            ],
            'items'        => [
                new UserActivityItem([]), // @todo implement this feature
                new UserActivityItem([]),
            ],
        ];
    }
}
