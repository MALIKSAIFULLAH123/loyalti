<?php

namespace MetaFox\Core\Http\Resources\v1\StatsContentType\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Models\StatsContentType as Model;

/**
 * Class StatsContentTypeItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class StatsContentTypeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $latestStat = $this->resource->latestStatistic;

        return [
            'id'                => $this->resource->id,
            'name'              => $this->resource->name,
            'icon'              => $this->resource->icon,
            'to'                => $this->resource->to,
            'label'             => $latestStat?->label,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
