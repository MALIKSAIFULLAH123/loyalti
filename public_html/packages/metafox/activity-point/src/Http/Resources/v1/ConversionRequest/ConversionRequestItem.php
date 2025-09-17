<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\ActivityPoint\Models\ConversionRequest as Model;
use MetaFox\ActivityPoint\Support\Browse\Traits\ConversionRequest\ExtraTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ConversionRequestItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ConversionRequestItem extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'activitypoint',
            'resource_name'     => $this->resource->entityType(),
            'points'            => number_format($this->resource->points),
            'status'            => $this->resource->status_text,
            'total'             => $this->resource->total_text,
            'fee'        => $this->resource->commission_text,
            'actual'            => $this->resource->actual_text,
            'reason'            => $this->resource->denied_reason,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'extra'             => $this->getExtra(),
        ];
    }
}
