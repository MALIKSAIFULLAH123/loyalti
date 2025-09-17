<?php

namespace MetaFox\Featured\Http\Resources\v1\Transaction\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Transaction as Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class TransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class TransactionItem extends JsonResource
{
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
            'user'          => ResourceGate::asItem($this->resource->userEntity, null),
            'item_title'    => $itemTitle,
            'item_link'     => $itemLink,
            'item_type_label' => $this->resource->item_type_label,
            'status'        => $this->resource->status_text,
            'payment_gateway' => ResourceGate::asEmbed($this->resource->paymentGateway, null),
            'price'         => $this->resource->price_formatted,
            'transaction_id' => $this->resource->transaction_id,
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
        ];
    }
}
