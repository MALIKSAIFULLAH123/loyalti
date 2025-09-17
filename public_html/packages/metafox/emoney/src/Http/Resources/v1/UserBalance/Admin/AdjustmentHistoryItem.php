<?php

namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Models\BalanceAdjustment as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class UserBalanceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AdjustmentHistoryItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->entityId(),
            'module_name' => 'ewallet',
            'resource_name' => $this->resource->entityType(),
            'sender' => ResourceGate::user($this->resource->userEntity),
            'amount' => app('currency')->getPriceFormatByCurrencyId($this->resource->currency, $this->resource->amount),
            'type' => $this->resource->type_text,
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
        ];
    }
}
