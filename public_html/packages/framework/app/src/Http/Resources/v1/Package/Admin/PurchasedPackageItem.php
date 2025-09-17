<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Platform\PackageManager;
use Illuminate\Support\Str;

/**
 * Class PackageItem.
 * @property array $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PurchasedPackageItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return $this->transformDataFromStore($this->resource);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed> $data
     */
    protected function transformDataFromStore(array $data): array
    {
        $packageId   = Arr::get($data, 'identity');
        $expiredAt   = Arr::get($data, 'expired_at', '');
        $pricingType = Arr::get($data, 'pricing_type');

        $package       = $packageId ? PackageManager::getInfo($packageId) : null;
        $expiredDay    = Carbon::parse($expiredAt)->startOfDay();

        $extra = [
            'current_version' => $package ? Arr::get($package, 'version', '5.0.0') : null,
            'is_expired'      => !empty($expiredAt) && Carbon::now()->gt($expiredDay),
            'pricing_type'    => Str::ucfirst($pricingType),
            'store_app_link'  => '/app/store/product/' . Arr::get($data, 'id'),
            'type'            => Str::headline(Arr::get($data, 'type')),
        ];

        return array_merge($data, $extra);
    }
}
