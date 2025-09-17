<?php

namespace MetaFox\Activity\Http\Resources\v1\ActivityHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Activity\Models\ActivityHistory as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class ActivityHistoryDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class ActivityHistoryDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $phrase = $this->resource->phrase;
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'feed',
            'resource_name' => $this->resource->entityType(),
        ];
    }
}
