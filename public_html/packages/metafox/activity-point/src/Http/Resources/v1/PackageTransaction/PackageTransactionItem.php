<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;
use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\StatusScope;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PackageTransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PackageTransactionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $price        = $this->resource->price;
        $userCurrency = $this->resource->currency_id;
        $priceString  = app('currency')->getPriceFormatByCurrencyId($userCurrency, $price);

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => 'activitypoint',
            'resource_name'        => 'package_transactions',
            'package_name'         => $this->resource->toTitle(),
            'package_price'        => $price,
            'package_price_string' => $priceString,
            'package_point'        => number_format($this->resource->points),
            'status'               => $this->getPaymentStatus($this->resource->status),
            'user'                 => ResourceGate::transactionUser($this->resource->userEntity),
            'user_id'              => $this->resource->user_id,
            'user_name'            => $this->resource->userEntity?->name,
            'user_link'            => $this->resource->userEntity?->toUrl(),
            'date'                 => $this->resource->created_at,
            'gateway'              => $this->resource->gateway?->title ?? __p('core::phrase.n_a'),
            'transaction_id'       => $this->resource->transaction_id,
            'extra'                => [
                'can_payment' => user()->can('pay', [$this->resource, $this->resource]),
            ],
            'iap' => app('events')->dispatch('resource.get_iap_product', [$this->resource->package, user()], true),
        ];
    }

    private function getPaymentStatus(string $key): string
    {
        $status = array_flip(StatusScope::STATUS_MAP_TEXT);
        $phrase = Arr::get($status, $key);

        return __p($phrase);
    }
}
