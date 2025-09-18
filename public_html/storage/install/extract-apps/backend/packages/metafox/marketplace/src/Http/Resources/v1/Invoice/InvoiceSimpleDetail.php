<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class InvoiceSimpleDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class InvoiceSimpleDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $createdDate = $this->resource->created_at;
        $createdDate = $createdDate ? Carbon::parse($createdDate)->format('c') : null;
        $price       = $this->handlePriceAndRecurringPriceLabel();

        return [
            [
                'label' => __p('user::phrase.user'),
                'value' => $this->resource->user?->full_name,
            ],
            [
                'label' => __p('core::phrase.title'),
                'value' => $this->getListingTitle(),
            ],
            [
                'label' => __p('core::phrase.price'),
                'value' => $price,
            ],
            [
                'label' => __p('core::phrase.status'),
                'value' => $this->resource->status_label,
            ],
            [
                'label'  => __p('core::phrase.creation_date'),
                'value'  => $createdDate,
                'type'   => 'time',
                'format' => 'LLL',
            ],
        ];
    }

    protected function getListingTitle(): ?string
    {
        $listing = null;

        if (null === $this->resource->listing) {
            $this->resource
                ->load(['listing' => fn ($item) => $item->withTrashed()]);
        }

        if (null !== $this->resource->listing) {
            $listing = ResourceGate::asEmbed($this->resource->listing, null);
        }

        return $listing?->toTitle();
    }

    protected function handlePriceAndRecurringPriceLabel(): string
    {
        $hasInitialFee = (float) $this->resource->price != 0;

        if ($hasInitialFee) {
            return app('currency')->getPriceFormatByCurrencyId(
                $this->resource->currency_id,
                $this->resource->price
            );
        }

        return __p('marketplace::phrase.free');
    }
}
