<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\Sponsor\ExtraTrait;
use MetaFox\Advertise\Traits\Sponsor\StatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SponsorItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class SponsorItem extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'              => $this->resource->entityId(),
            'module_name'     => 'advertise',
            'resource_name'   => $this->resource->entityType(),
            'title'           => $this->resource->toTitle(),
            'link'            => $this->resource->toLink(),
            'router'          => $this->resource->toRouter(),
            'url'             => $this->resource->toUrl(),
            'status'          => $this->resource->status_text,
            'start_date'      => $this->toDate($this->resource->start_date),
            'end_date'        => $this->toDate($this->resource->end_date),
            'payment_price'   => $this->getPaymentPrice(),
            'is_active'       => $this->resource->is_active,
            'is_sponsor_feed' => $this->resource->sponsor_type == Support::SPONSOR_TYPE_FEED,
            'statistic'       => $this->getStatistics(),
            'extra'           => $this->getExtra(),
        ];
    }

    protected function toDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }

    protected function getPaymentPrice(): ?string
    {
        if ($this->resource->status != Support::ADVERTISE_STATUS_UNPAID) {
            return null;
        }

        $context = user();

        $currencyId = app('currency')->getUserCurrencyId($context);

        if (Facade::isSponsorChangePrice($this->resource)) {
            $price = Facade::getCurrentSponsorPrice($this->resource);

            if (!is_numeric($price)) {
                return null;
            }

            return $this->formatPrice($currencyId, Facade::calculateSponsorPrice($this->resource, $price));
        }

        if (null === $this->resource->latestUnpaidInvoice) {
            return null;
        }

        return $this->formatPrice($this->resource->latestUnpaidInvoice->currency_id, $this->resource->latestUnpaidInvoice->price);
    }

    protected function formatPrice(string $currencyId, float $price): ?string
    {
        return app('currency')->getPriceFormatByCurrencyId($currencyId, $price);
    }
}
