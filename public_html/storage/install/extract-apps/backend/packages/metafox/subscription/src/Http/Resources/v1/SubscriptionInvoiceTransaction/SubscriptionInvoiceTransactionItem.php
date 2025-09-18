<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoiceTransaction;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Subscription\Support\Helper;

class SubscriptionInvoiceTransactionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $transaction = $this->resource;

        $amount = null;

        if (null !== $transaction->paid_price) {
            $amount = app('currency')->getPriceFormatByCurrencyId(
                $transaction->currency,
                $transaction->paid_price
            );
        }

        return [
            'id'             => $transaction->entityId(),
            'module_name'    => 'subscription',
            'resource_name'  => 'subscription_invoice_transaction',
            'amount'         => $amount,
            'payment_method' => null !== $transaction->gateway ? $transaction->gateway->title : null,
            'payment_status' => Helper::getTransactionPaymentStatus($transaction->payment_status),
            'transaction_id' => $transaction->transaction_id,
            'created_at'     => Carbon::parse($transaction->created_at)->format('c'),
        ];
    }
}
