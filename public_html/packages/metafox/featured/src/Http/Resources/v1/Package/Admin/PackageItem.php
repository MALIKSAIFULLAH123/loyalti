<?php

namespace MetaFox\Featured\Http\Resources\v1\Package\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Featured\Models\Package as Model;
use MetaFox\Featured\Traits\Package\ExtraTrait;
use MetaFox\Featured\Traits\Package\StatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PackageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PackageItem extends JsonResource
{
    use ExtraTrait;
    use StatisticTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'featured',
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->toTitle(),
            'duration_text' => $this->resource->duration_text,
            'is_active'     => $this->resource->is_active,
            'is_free'       => $this->resource->is_free,
            'statistic'     => $this->getStatistic(),
            'extra'         => $this->getExtra(),
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
        ];
    }
}
