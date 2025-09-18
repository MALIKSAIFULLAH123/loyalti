<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SubscriptionInvoiceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class SubscriptionInvoiceItem extends SubscriptionInvoiceDetail
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $package = $this->resource->package;

        $context = user();

        [$price, $recurringPrice] = $this->handlePriceAndRecurringPriceLabel();

        $roles = SubscriptionPackage::getRoleOptionsOnSuccess(true);

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => Helper::MODULE_NAME,
            'resource_name'        => $this->resource->entityType(),
            'package_title'        => $package->title,
            'price'                => $price,
            'recurring_price'      => $recurringPrice,
            'payment_status'       => $this->resource->payment_status,
            'payment_status_label' => Helper::getPaymentStatusLabel($this->resource->payment_status),
            'upgraded_membership'  => Arr::get($roles, $package->upgraded_role_id),
            'image'                => $package->images,
            'payment_buttons'      => SubscriptionInvoice::getPaymentButtons($context, $this->resource),
            'created_at'           => $this->resource->created_at,
            'activated_at'         => $this->resource->activated_at,
            'expired_at'           => $this->resource->expired_at,
            'expired_description'  => $this->resource->expired_description,
            'extra'                => $this->getExtra(),
            'iap'                  => app('events')->dispatch('resource.get_iap_product', [$this->resource->package, $context], true),
        ];
    }
}
