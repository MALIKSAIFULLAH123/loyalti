<?php

namespace MetaFox\Featured\Http\Resources\v1\Item;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Item as Model;
use MetaFox\Featured\Traits\Item\ExtraTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ItemItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ItemItem extends JsonResource
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
            $itemLink  = $this->resource->item->toLink();
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'featured',
            'resource_name' => $this->resource->entityType(),
            'item_title'    => $itemTitle,
            'item_link'     => $itemLink,
            'item_type_label' => $this->resource->item_type_label,
            'package'       => ResourceGate::asEmbed($this->resource->package, null),
            'is_free'       => $this->resource->is_free,
            'pricing'       => $this->resource->pricing,
            'status'        => $this->resource->status_text,
            'duration'      => $this->resource->duration,
            'extra'         => $this->getExtra(),
            'price'         => $this->getPriceForPayment(),
            'expiration_date' => is_string($this->resource->expired_at) ? Carbon::parse($this->resource->expired_at)->toISOString() : null,
            'creation_date' => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
        ];
    }

    protected function getPriceForPayment(): ?string
    {
        $info = $this->resource->payment_information;

        if (!is_array($info)) {
            return null;
        }

        return Feature::getPriceFormatted(Arr::get($info, 'price'), Arr::get($info, 'currency'));
    }
}
