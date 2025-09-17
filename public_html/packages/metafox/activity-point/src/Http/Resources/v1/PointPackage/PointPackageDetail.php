<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class PointPackageDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class PointPackageDetail extends JsonResource
{
    use HasStatistic;
    use HasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $price        = $this->resource->price;
        $userCurrency = app('currency')->getUserCurrencyId(user());
        $userPrice    = Arr::get($price, $userCurrency, 0.0);
        $priceString  = app('currency')->getPriceFormatByCurrencyId($userCurrency, $userPrice);

        return [
            'id'              => $this->resource->entityId(),
            'module_name'     => 'activitypoint',
            'resource_name'   => $this->resource->entityType(),
            'title'           => $this->resource->title,
            'image'           => $this->resource->images,
            'amount'          => $this->resource->amount,
            'price_list'      => $price,
            'price_string'    => $priceString,
            'is_active'       => $this->resource->is_active,
            'statistic'       => $this->getStatistic(),
            'extra'           => $this->getExtra(),
            'creation_date'   => $this->resource->created_at,
            'moderation_date' => $this->resource->updated_at,
            'iap'             => app('events')->dispatch('resource.get_iap_product', [$this->resource, user()], true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_purchase' => $this->resource->total_purchase,
        ];
    }
}
