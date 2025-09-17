<?php
namespace MetaFox\Payment\Support;

use Illuminate\Support\Arr;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;

class Support
{
    public function getPaymentTypeOptions(): array
    {
        return [
            [
                'label' => __p('payment::phrase.one_time'),
                'value' => Payment::PAYMENT_ONETIME,
            ],
            [
                'label' => __p('payment::phrase.recurring'),
                'value' => Payment::PAYMENT_RECURRING,
            ],
        ];
    }

    public function getOrderStatusOptions(): array
    {
        $options = [];

        foreach (Order::ALLOW_STATUS as $label => $value) {
            $options[] = [
                'label' => __p($label),
                'value' => $value,
            ];
        }

        return $options;
    }

    public function getRecurringStatusOptions(): array
    {
        $options = [];

        foreach (Order::ALLOW_RECURRING_STATUS as $label => $value) {
            $options[] = [
                'label' => __p($label),
                'value' => $value,
            ];
        }

        return $options;
    }

    public function getStatusText(string $status): string
    {
        $options = array_combine(array_values(Order::ALLOW_STATUS), array_keys(Order::ALLOW_STATUS));

        $label = Arr::get($options, $status);

        if (null === $label) {
            return __p('core::phrase.n_a');
        }

        return __p($label);
    }

    public function getRecurringStatusText(string $status): ?string
    {
        if ($status == Order::RECURRING_STATUS_UNSET) {
            return null;
        }

        $options = array_combine(array_values(Order::ALLOW_RECURRING_STATUS), array_keys(Order::ALLOW_RECURRING_STATUS));

        $label = Arr::get($options, $status);

        if (null === $label) {
            return __p('core::phrase.n_a');
        }

        return __p($label);
    }

    public function getTransactionStatusOptions(): array
    {
        return [
            [
                'label' => __p('payment::phrase.recurring_status_pending'),
                'value' => Transaction::STATUS_PENDING,
            ],
            [
                'label' => __p('payment::phrase.status_completed'),
                'value' => Transaction::STATUS_COMPLETED,
            ],
            [
                'label' => __p('payment::phrase.status_failed'),
                'value' => Transaction::STATUS_FAILED,
            ],
        ];
    }
}
