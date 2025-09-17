<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\InAppPurchase\Models\Product as Model;
use MetaFox\Platform\Facades\Settings;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class ProductDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class ProductDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $price        = $this->resource->price ?? [];
        $context      = user();
        $userCurrency = app('currency')->getUserCurrencyId($context);
        $userPrice    = Arr::get($price, $userCurrency, 0.00);
        $priceString  = app('currency')->getPriceFormatByCurrencyId($userCurrency, $userPrice);

        return [
            'id'                 => $this->resource->entityId(),
            'module_name'        => $this->resource->moduleName(),
            'resource_name'      => $this->resource->entityType(),
            'item_id'            => $this->resource->item_id,
            'item_type'          => $this->resource->item_type,
            'title'              => $this->resource->toTitle(),
            'type'               => $this->resource->toType(),
            'url'                => $this->resource->toUrl(),
            'price'              => $priceString,
            'is_recurring'       => $this->resource->is_recurring,
            'ios_product_id'     => Settings::get('in-app-purchase.enable_iap_ios') ? $this->resource->ios_product_id : null,
            'android_product_id' => Settings::get('in-app-purchase.enable_iap_android') ? $this->resource->android_product_id : null,
        ];
    }
}
