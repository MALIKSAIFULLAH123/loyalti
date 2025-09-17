<?php

namespace MetaFox\Payment\Http\Resources\v1\Transaction\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Payment\Models\Transaction as Model;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Models\UserEntity;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class TransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class TransactionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $user = [
            'display_name' => __p('payment::admin.deleted_user'),
        ];

        if ($this->resource->userEntity instanceof UserEntity) {
            $user = ResourceGate::asItem($this->resource->userEntity);
        }

        return [
            'id' => $this->resource->entityId(),
            'module_name' => 'payment',
            'resource_name' => $this->resource->entityType(),
            'user' => $user,
            'gateway' => $this->resource->gateway?->title ?? __p('core::phrase.n_a'),
            'total' => app('currency')->getPriceFormatByCurrencyId($this->resource->currency, $this->resource->amount),
            'status' => $this->resource->status_text,
            'gateway_transaction_id' => $this->resource->gateway_transaction_id,
            'created_at' => Carbon::parse($this->resource->created_at)->toISOString(),
            'link' => $this->resource->toAdminCPLink(),
        ];
    }
}
