<?php

namespace MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\ConversionRate;
use MetaFox\EMoney\Models\CurrencyConverter;
use MetaFox\EMoney\Support\Support;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ConversionRateItem.
 * @property ConversionRate $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin ConversionRate
 */
class ConversionRateItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $converterTitle = $converterLink = null;

        if ($this->resource->type == Support::TARGET_EXCHANGE_RATE_TYPE_AUTO && $this->resource->log?->converter instanceof CurrencyConverter) {
            $converterTitle = $this->resource->log->converter->title;
            $converterLink  = $this->resource->log->converter->link;
        }

        return [
            'id'                            => $this->resource->entityId(),
            'module_name'                   => Emoney::getAppAlias(),
            'resource_name'                 => $this->getResourceName(),
            'base'                          => $this->resource->base,
            'target'                        => $this->resource->target,
            'exchange_rate'                 => $this->resource->exchange_rate,
            'type'                          => $this->resource->type,
            'modification_date'             => Carbon::parse($this->resource->updated_at)->toISOString(),
            'is_synchronized'               => $this->resource->is_synchronized,
            'auto_synchronized_source'      => $converterTitle,
            'auto_synchronized_source_link' => $converterLink,
        ];
    }

    private function getResourceName(): string
    {
        if (Emoney::isUsingNewAlias()) {
            return $this->resource->entityType();
        }

        return 'emoney_conversion_rate';
    }
}
