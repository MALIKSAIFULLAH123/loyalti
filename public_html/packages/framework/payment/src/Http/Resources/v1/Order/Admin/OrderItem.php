<?php

namespace MetaFox\Payment\Http\Resources\v1\Order\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Payment\Models\Order as Model;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class OrderItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class OrderItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $buyer = [
            'display_name' => __p('payment::admin.deleted_user'),
        ];

        $seller = [
            'display_name' => __p('core::phrase.system'),
        ];

        if (null !== $this->resource->userEntity) {
            $buyer = ResourceGate::asItem($this->resource->userEntity);
        }

        if ($this->resource->payee_id && $this->resource->payee_type) {
            $seller = [
                'display_name' => __p('payment::admin.deleted_user'),
            ];

            if (null !== $this->resource->payeeEntity) {
                $seller = ResourceGate::asItem($this->resource->payeeEntity);
            }
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'payment',
            'resource_name' => $this->resource->entityType(),
            'buyer'         => $buyer,
            'app'           => $this->getApp(),
            'seller'        => $seller,
            'item'          => $this->getItem(),
            'gateway'       => $this->resource->gateway?->title ?? __p('core::phrase.n_a'),
            'type'          => $this->resource->payment_type_text,
            'total'         => $this->resource->total_text,
            'status'        => $this->resource->status_text,
            'recurring_status' => $this->resource->recurring_status_text,
            'gateway_order_id' => $this->resource->gateway_order_id,
            'gateway_subscription_id' => $this->resource->gateway_subscription_id,
            'created_at'    => Carbon::parse($this->resource->created_at)->toISOString(),
        ];
    }

    private function getApp(): ?string
    {
        if (null === $this->resource->item) {
            return null;
        }

        $alias = getAliasByEntityType($this->resource->item->entityType());

        if (null === $alias) {
            return null;
        }

        $key = sprintf('%s::phrase.app_name', $alias);

        $label = __p($key);

        if ($label == $key) {
            return null;
        }

        return $label;
    }

    private function getItem(): ?array
    {
        $item = $this->resource->item;

        if (null === $item) {
            return null;
        }

        $url = null;

        if ($item instanceof HasUrl) {
            $url = $item->toUrl();

            if (method_exists($item, 'toAdminCPUrl')) {
                $url = $item->toAdminCPUrl();
            }
        }

        return [
            'title' => $item instanceof HasTitle ? $item->toTitle() : $this->resource->title,
            'url'   => $url,
        ];
    }
}
