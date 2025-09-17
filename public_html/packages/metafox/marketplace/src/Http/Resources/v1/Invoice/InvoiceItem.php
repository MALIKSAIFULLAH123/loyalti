<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
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
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->getModuleName(),
            'resource_name'     => $this->resource->entityType(),
            'buyer'             => ResourceGate::embed($this->resource->user),
            'seller'            => ResourceGate::embed($this->resource->listing?->user),
            'listing'           => $this->getListing(),
            'status'            => $this->resource->status,
            'status_label'      => $this->resource->status_label,
            'status_info'       => ListingFacade::getStatusInfo($this->resource->status),
            'price'             => ListingFacade::getPriceFormat($this->resource->currency, $this->resource->price),
            'payment_buttons'   => $this->getPaymentButtons(),
            'creation_date'     => $this->getCreationDate(),
            'modification_date' => $this->getModificationDate(),
            'payment_date'      => $this->getPaymentDate(),
            'extra'             => $this->getExtra(),
            'url'               => $this->resource->toUrl(),
        ];
    }
}
