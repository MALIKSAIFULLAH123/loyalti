<?php

namespace MetaFox\Marketplace\Http\Resources\v1\InvoiceTransaction;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;

class TransactionItem extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'resource_name'     => $this->resource->entityType(),
            'module_name'       => $this->getModuleName(),
            'status'            => $this->resource->status_label,
            'price'             => ListingFacade::getPriceFormat($this->resource->currency_id, $this->resource->price),
            'transaction_id'    => $this->resource->transaction_id,
            'payment_method'    => $this->getPaymentMethod(),
            'creation_date'     => $this->getCreationDate(),
            'modification_date' => $this->getModificationDate(),
        ];
    }

    public function getPaymentMethod(): ?string
    {
        $gateway = $this->resource->gateway;

        if (null === $gateway) {
            return null;
        }

        return $gateway->title;
    }

    protected function getCreationDate(): string
    {
        return Carbon::parse($this->resource->created_at)->format('c');
    }

    protected function getModificationDate(): string
    {
        return Carbon::parse($this->resource->updated_at)->format('c');
    }

    protected function getModuleName(): string
    {
        return 'marketplace';
    }
}
