<?php

namespace MetaFox\Featured\Http\Resources\v1\Invoice\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Invoice as Model;
use MetaFox\Featured\Traits\Invoice\Admin\ExtraTrait;
use MetaFox\Platform\Contracts\Content;
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
class InvoiceItem extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $itemTitle = $this->resource->deleted_item_title;
        $itemLink  = null;

        if ($this->resource->item instanceof Content) {
            $itemTitle = Feature::getItemTitle($this->resource->item);
            $itemLink  = $this->resource->item->toUrl();
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'featured',
            'resource_name' => $this->resource->entityType(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'item_title'    => $itemTitle,
            'item_link'     => $itemLink,
            'item_type_label' => $this->resource->item_type_label,
            'package'       => ResourceGate::asEmbed($this->resource->package, null),
            'status'        => $this->resource->status_information,
            'price'         => $this->resource->price_formatted,
            'payment_gateway' => ResourceGate::asEmbed($this->resource->paymentGateway, null),
            'transaction_id' => $this->resource->paidTransaction?->transaction_id,
            'extra'         => $this->getExtra(),
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
        ];
    }
}
