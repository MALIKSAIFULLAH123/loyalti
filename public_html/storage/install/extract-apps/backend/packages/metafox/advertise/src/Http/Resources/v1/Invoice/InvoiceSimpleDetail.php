<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Advertise\Models\Invoice as Model;

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
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $createdDate = $this->resource->created_at;
        $createdDate = $createdDate ? Carbon::parse($createdDate)->format('c') : null;
        $price       = $this->handlePriceAndRecurringPriceLabel();

        return [
            [
                'label' => __p('advertise::phrase.user'),
                'value' => $this->resource->user?->full_name,
            ],
            [
                'label' => __p('advertise::phrase.item_title'),
                'value' => $this->resource->item->toTitle(),
            ],
            [
                'label' => __p('core::phrase.price'),
                'value' => $price,
            ],
            [
                'label' => __p('core::phrase.status'),
                'value' => $this->resource->payment_status_label,
            ],
            [
                'label'  => __p('advertise::phrase.created_date'),
                'value'  => $createdDate,
                'type'   => 'time',
                'format' => 'LLL',
            ],
        ];
    }

    protected function handlePriceAndRecurringPriceLabel(): string
    {
        $hasInitialFee = (float)$this->resource->price != 0;

        if ($hasInitialFee) {
            return app('currency')->getPriceFormatByCurrencyId(
                $this->resource->currency_id,
                $this->resource->price
            );
        }

        return __p('advertise::phrase.free');
    }
}
