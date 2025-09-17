<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice\Admin;

use MetaFox\Marketplace\Http\Resources\v1\Invoice\InvoiceDetail as JsonResource;
use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Support\Browse\Traits\Invoice\ExtraTrait;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class InvoiceDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class InvoiceDetail extends JsonResource
{
    use ExtraTrait;

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
            'buyer'             => ResourceGate::user($this->resource->userEntity),
            'seller'            => ResourceGate::user($this->resource->listing?->userEntity),
            'listing'           => $this->getListing(),
            'status'            => $this->resource->status,
            'status_label'      => $this->resource->status_label,
            'price'             => ListingFacade::getPriceFormat($this->resource->currency, $this->resource->price),
            'transactions'      => $this->getTransactions(),
            'payment_buttons'   => $this->getPaymentButtons(),
            'creation_date'     => $this->getCreationDate(),
            'modification_date' => $this->getModificationDate(),
            'payment_date'      => $this->getPaymentDate(),
            'extra'             => $this->getExtra(),
            'detail'            => $this->resource->toAdmincpUrl(),
        ];
    }
}
