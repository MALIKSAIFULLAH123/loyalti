<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\Advertise\ExtraTrait;
use MetaFox\Advertise\Traits\Advertise\StatisticTrait;
use MetaFox\Localize\Http\Resources\v1\Country\Admin\CountryItemCollection;
use MetaFox\Localize\Http\Resources\v1\Language\Admin\LanguageItemCollection;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Http\Resources\v1\UserGender\Admin\UserGenderItemCollection;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class AdvertiseDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class AdvertiseDetail extends JsonResource
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
            'image'           => $this->resource->images,
            'is_active'       => $this->resource->is_active,
            'genders'         => $this->getGenders(),
            'locations'       => $this->getLocations(),
            'placement'       => $this->getPlacement(),
            'languages'       => $this->getLanguages(),
            'start_date'      => $this->toDate($this->resource->start_date),
            'end_date'        => $this->toDate($this->resource->end_date),
            'status'          => $this->resource->status_text,
            'is_denied'       => $this->resource->is_denied,
            'is_pending'      => $this->resource->is_pending,
            'is_unpaid'       => $this->resource->is_unpaid,
            'link'            => $this->resource->toLink(),
            'url'             => $this->resource->toUrl(),
            'statistic'       => $this->getStatistics(),
            'extra'           => $this->getExtra(),
            'age_from'        => $this->resource->age_from,
            'age_to'          => $this->resource->age_to,
            'creation_type'   => $this->resource->creation_type,
            'image_values'    => $this->resource->image_values,
            'html_values'     => $this->resource->html_values,
            'destination_url' => $this->resource->url,
            'advertise_type'  => $this->resource->advertise_type,
            'payment_price'   => $this->getPaymentPrice(),
            'created_at'      => $this->toDate($this->resource->created_at),
            'updated_at'      => $this->toDate($this->resource->updated_at),
            'status_info'     => $this->resource->status_information,
        ];
    }

    protected function getLocations(): ?ResourceCollection
    {
        if (!Settings::get('advertise.enable_advanced_filter', false)) {
            return null;
        }

        $locations = $this->resource->countries;

        if (!$locations->count()) {
            return null;
        }

        return new CountryItemCollection($locations);
    }

    protected function getGenders(): ?ResourceCollection
    {
        $ids = $this->resource->genders()->allRelatedIds()->toArray();

        $collection = resolve(UserGenderRepositoryInterface::class)->viewAllGenders($ids);

        return new UserGenderItemCollection($collection);
    }

    protected function getLanguages(): ?ResourceCollection
    {
        $ids = $this->resource->languages()->allRelatedIds()->toArray();

        $collection = resolve(LanguageRepositoryInterface::class)->viewAllLanguages($ids);

        return new LanguageItemCollection($collection);
    }

    protected function toDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }

    protected function getPlacement(): ?JsonResource
    {
        if (null === $this->resource->placement) {
            return null;
        }

        return ResourceGate::asEmbed($this->resource->placement, null);
    }

    protected function getPaymentPrice(): ?string
    {
        if ($this->resource->status != Support::ADVERTISE_STATUS_UNPAID) {
            return null;
        }

        $context = user();

        $currencyId = app('currency')->getUserCurrencyId($context);

        if (Facade::isAdvertiseChangePrice($this->resource)) {
            $placementPrice = Facade::getPlacementPriceByCurrencyId($this->resource->placement_id, $currencyId);

            if (null === $placementPrice) {
                return null;
            }

            return $this->formatPrice($currencyId, Facade::calculateAdvertisePrice($this->resource, $placementPrice));
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
