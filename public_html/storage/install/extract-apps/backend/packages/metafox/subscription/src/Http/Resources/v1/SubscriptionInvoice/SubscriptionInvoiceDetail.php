<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice;
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
class SubscriptionInvoiceDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $context = user();

        $package = $this->resource->package;

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
            'transactions'         => SubscriptionInvoice::getTransactions($this->resource->entityId()),
            'table_fields'         => SubscriptionInvoice::getTableFields(),
            'payment_buttons'      => SubscriptionInvoice::getPaymentButtons($context, $this->resource),
            'created_at'           => $this->convertDateTime($this->resource->created_at),
            'activated_at'         => $this->convertDateTime($this->resource->activated_at),
            'expired_at'           => $this->convertDateTime($this->resource->expired_at),
            'expired_description'  => $this->resource->expired_description,
            'extra'                => $this->getExtra(),
            'iap'                  => app('events')->dispatch('resource.get_iap_product', [$this->resource->package, $context], true),
        ];
    }

    public function getExtra(): array
    {
        /** @var SubscriptionInvoicePolicy $policy */
        $policy  = PolicyGate::getPolicyFor(Model::class);
        $context = user();

        return [
            'can_cancel'  => $policy->cancel($context, $this->resource),
            'can_upgrade' => $policy->upgrade($context, $this->resource),
        ];
    }
    protected function convertDateTime(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }

    protected function handlePriceAndRecurringPriceLabel(): array
    {
        $package = $this->resource->package;

        $recurringPrice = null;

        $isFreeInitial = 0 == (float) $this->resource->initial_price;

        $hasRecurringPrice = null !== $this->resource->recurring_price;

        switch ($isFreeInitial) {
            case true:
                $price = match ($hasRecurringPrice) {
                    true => app('currency')->getPriceFormatByCurrencyId(
                        $this->resource->currency,
                        $this->resource->recurring_price
                    ),
                    false => __p('subscription::phrase.free'),
                };

                $recurringPrice = match ($hasRecurringPrice) {
                    true  => __p('subscription::phrase.free_with_first_period', ['period' => strtolower(Helper::getPeriodLabel($package->recurring_period))]),
                    false => null,
                };

                break;
            default:
                $price = app('currency')->getPriceFormatByCurrencyId(
                    $this->resource->currency,
                    $this->resource->initial_price
                );

                if ($hasRecurringPrice) {
                    $recurringPrice = app('currency')->getPriceFormatByCurrencyId(
                        $this->resource->currency,
                        $this->resource->recurring_price
                    );

                    if ($package->recurring_period) {
                        $period         = Helper::getPeriodLabel($package->recurring_period);

                        $recurringPrice = __p('subscription::phrase.recurring_price_info', [
                            'price'  => $recurringPrice,
                            'period' => $period,
                        ]);

                        break;
                    }

                    $recurringPrice = __p('subscription::phrase.recurring_price', [
                        'price' => $recurringPrice,
                    ]);
                }

                break;
        }

        return [$price, $recurringPrice];
    }
}
