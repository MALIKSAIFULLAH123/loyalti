<?php

namespace MetaFox\Payment\Http\Resources\v1\Transaction\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction as Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\User\Models\UserEntity;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class TransactionDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class TransactionDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        if (null === $this->resource->order) {
            return $this->getTransactionDetail();
        }

        return array_merge($this->getTransactionDetail(), [
            [
                'type' => 'divider'
            ],
        ], $this->getOrderDetail());
    }

    private function getOrderDetail(): array
    {
        /**
         * @var Order $order
         */
        $order = $this->resource->order;

        if (null === $order) {
            return [];
        }

        $item = __p('core::phrase.deleted');

        if ($order->item instanceof Entity) {
            $item = $order->title;

            if ($order->item instanceof HasTitle) {
                $item = $order->item->toTitle();
            }
        }

        $params = [
            [
                'type' => 'typo',
                'value' => __p('payment::admin.order_detail'),
                'props' => [
                    'variant' => 'h4',
                ],
            ],
            [
                'label' => __p('payment::admin.item'),
                'value' => $item,
                'type'  => 'link',
                'link'  => $order->item instanceof HasUrl ? $order->item->toUrl() : null,
                'target' => '_blank'
            ],
        ];

        if (null !== $order->payeeEntity) {
            $params[] = [
                [
                    'label' => __p('payment::admin.seller'),
                    'value'  => $order->payeeEntity->display_name,
                    'type' => 'link',
                    'link' => $order->payeeEntity->toUrl(),
                    'target' => '_blank',
                ],
            ];
        }

        if (null !== ($app = $this->getApp())) {
            $params[] = [
                [
                    'label' => __p('core::phrase.app'),
                    'value'  => $app,
                ],
            ];
        }

        $params = array_merge($params, [
            [
                'label' => __p('payment::admin.type'),
                'value'  => $order->payment_type_text,
            ],
            [
                'label' => __p('core::phrase.total'),
                'value'  => app('currency')->getPriceFormatByCurrencyId($order->currency, $order->total),
            ],
            [
                'label' => __p('core::phrase.status'),
                'value' => $order->status_text,
            ],
        ]);

        if ($order->isRecurringOrder()) {
            $params[] = [
                'label' => __p('payment::admin.subscription_status'),
                'value' => $order->recurring_status_text,
            ];
        }

        $params[] = [
            'label' => __p('payment::admin.order_id'),
            'value' => $order->gateway_order_id,
        ];

        if ($order->isRecurringOrder() && null !== $order->gateway_subscription_id) {
            $params[] = [
                'label' => __p('payment::admin.subscription_id'),
                'value' => $order->gateway_subscription_id,
            ];
        }

        $params[] = [
            'label' => __p('core::phrase.creation_date'),
            'value' => Carbon::parse($order->created_at)->toISOString(),
            'type'   => 'time',
            'format' => 'LLL',
        ];

        return $params;
    }

    private function getApp(): ?string
    {
        if (null === $this->resource->order?->item) {
            return null;
        }

        $alias = getAliasByEntityType($this->resource->order->item->entityType());

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

    private function getTransactionDetail(): array
    {
        return [
            [
                'type' => 'typo',
                'value' => __p('payment::admin.transaction_detail'),
                'props' => [
                    'variant' => 'h4'
                ],
            ],
            [
                'label' => __p('user::phrase.user'),
                'value'  => $this->resource->userEntity?->display_name ?? __p('payment::admin.deleted_user'),
                'type' => 'link',
                'link' => $this->resource->userEntity?->toUrl(),
                'target' => '_blank',
            ],
            [
                'label' => __p('payment::admin.payment_gateway'),
                'value'  => $this->resource->gateway?->title ?? __p('core::phrase.n_a'),
            ],
            [
                'label' => __p('core::phrase.total'),
                'value'  => app('currency')->getPriceFormatByCurrencyId($this->resource->currency, $this->resource->amount),
            ],
            [
                'label' => __p('core::phrase.status'),
                'value' => $this->resource->status_text,
            ],
            [
                'label' => __p('payment::phrase.transaction_id'),
                'value' => $this->resource->gateway_transaction_id,
            ],
            [
                'label' => __p('core::phrase.creation_date'),
                'value' => Carbon::parse($this->resource->created_at)->toISOString(),
                'type'   => 'time',
                'format' => 'LLL',
            ],
        ];
    }
}
