<?php

namespace MetaFox\Advertise\Http\Resources\v1\InvoiceTransaction;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Advertise\Models\InvoiceTransaction as Model;

/**
 * @property Model $resource
 *
 */
class InvoiceTransactionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $transaction = $this->resource;

        $amount = null;

        if (null !== $transaction?->price) {
            $amount = app('currency')->getPriceFormatByCurrencyId(
                $transaction?->currency_id,
                $transaction?->price
            );
        }

        return [
            'id'             => $transaction->entityId(),
            'module_name'    => 'advertise',
            'resource_name'  => 'invoice_transaction',
            'amount'         => $amount,
            'payment_method' => $transaction?->invoice?->gateway?->title ?? null,
            'status'         => $transaction?->status_label,
            'transaction_id' => $transaction?->transaction_id,
            'created_at'     => Carbon::parse($transaction?->created_at)->format('c'),
        ];
    }
}
