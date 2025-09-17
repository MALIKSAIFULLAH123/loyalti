<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PointPackageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PointPackageItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->resource->entityId(),
            'module_name'     => 'activitypoint',
            'resource_name'   => $this->resource->entityType(),
            'title'           => $this->resource->title,
            'amount'          => $this->resource->amount,
            'price_string'    => $this->getPriceFormatted(),
            'is_active'       => $this->resource->is_active,
            'creation_date'   => $this->resource->created_at,
            'moderation_date' => $this->resource->updated_at,
            'links'           => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }

    protected function getPriceFormatted(): ?string
    {
        $context = user();

        if (!ActivityPoint::isPackageAvailableForPurchase($context, $this->resource)) {
            return null;
        }

        $userCurrency = app('currency')->getUserCurrencyId($context);

        $price     = $this->resource->price ?? [];
        $userPrice = Arr::get($price, $userCurrency);

        return app('currency')->getPriceFormatByCurrencyId($userCurrency, $userPrice);
    }
}
