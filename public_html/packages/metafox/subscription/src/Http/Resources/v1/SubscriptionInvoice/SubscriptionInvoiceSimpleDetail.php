<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class SubscriptionInvoiceDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class SubscriptionInvoiceSimpleDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $activationDate = $this->resource->activated_at;

        if (null !== $activationDate) {
            $activationDate = Carbon::parse($activationDate)->format('c');
        }

        $expirationDate = $this->resource->expired_at;

        if (null !== $expirationDate) {
            $expirationDate = Carbon::parse($expirationDate)->format('c');
        }

        $price = $this->handlePriceAndRecurringPriceLabel();

        $roles = SubscriptionPackage::getRoleOptionsOnSuccess(true);

        return [
            [
                'label' => __p('subscription::admin.user'),
                'value' => $this->resource->user?->full_name,
            ],
            [
                'label' => __p('subscription::admin.package_title'),
                'value' => $this->resource->package?->title,
            ],
            [
                'label' => __p('subscription::admin.price'),
                'value' => $price,
            ],
            [
                'label' => __p('subscription::admin.subscription_status'),
                'value' => Helper::getPaymentStatusLabel($this->resource->payment_status),
            ],
            [
                'label' => __p('subscription::admin.acquired_membership'),
                'value' => Arr::get($roles, $this->resource->package->upgraded_role_id),
            ],
            [
                'label'  => __p('subscription::admin.activation_date'),
                'value'  => $activationDate,
                'type'   => 'time',
                'format' => 'LLL',
            ],
            [
                'label'  => __p('subscription::admin.expiration_date'),
                'value'  => $expirationDate,
                'type'   => 'time',
                'format' => 'LLL',
            ],
        ];
    }

    protected function handlePriceAndRecurringPriceLabel(): string
    {
        $hasInitialFee = (float)$this->resource->initial_price != 0;
        $hasRecurringPrice = null !== $this->resource->recurring_price;

        if ($hasInitialFee) {
            return app('currency')->getPriceFormatByCurrencyId(
                $this->resource->currency,
                $this->resource->initial_price
            );
        }

        if ($hasRecurringPrice) {
            return app('currency')->getPriceFormatByCurrencyId(
                $this->resource->currency,
                $this->resource->recurring_price
            );
        }

        return __p('subscription::phrase.free');
    }
}
