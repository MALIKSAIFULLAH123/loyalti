<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Invoice;

use Foxexpert\Sevent\Models\Invoice as Model;
use Foxexpert\Sevent\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class InvoiceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class InvoiceItem extends InvoiceDetail
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $price = app('currency')->getPriceFormatByCurrencyId($this->resource->currency, (float)$this->resource->price);
        
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->getModuleName(),
            'resource_name'     => $this->resource->entityType(),
            'buyer'             => ResourceGate::asEmbed($this->resource->user),
            'seller'            => ResourceGate::asEmbed($this->resource->sevent?->user),
            'sevent'          => $this->getSevent(),
            'ticket'          => $this->getTicket(),
            'status'            => $this->resource->status,
            'qty'            => $this->resource->qty,
            'status_label'      => $this->resource->status_label,
            'price'             => $price,
            'creation_date'     => $this->getCreationDate(),
            'modification_date' => $this->getModificationDate(),
            'payment_date'      => $this->getPaymentDate(),
            'extra'             => $this->getExtra(),
        ];
    }
}
