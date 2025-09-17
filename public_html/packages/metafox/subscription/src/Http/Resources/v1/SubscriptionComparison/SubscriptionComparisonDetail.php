<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use MetaFox\Subscription\Support\Browse\Traits\SubscriptionComparison\ExtraTrait;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;
use MetaFox\User\Support\Facades\User;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class SubscriptionComparisonDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class SubscriptionComparisonDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => Helper::MODULE_NAME,
            'resource_name' => Helper::COMPARISON_RESOURCE_FILTER_NAME,
            'title'         => $this->resource->toTitle(),
            'packages'      => $this->handlePackages(),
        ];
    }

    protected function handlePackages(): array
    {
        $packages = $this->resource->packages;

        $context = Auth::guest() ? User::getGuestUser() : user();

        $allPackages = SubscriptionPackage::getPackages($context, [
            'view' => Helper::VIEW_FILTER,
        ]);

        $convertedPackages = [];

        if (null !== $allPackages) {
            if (null !== $packages) {
                $packages = $packages->toArray();
                $packages = array_combine(Arr::pluck($packages, 'pivot.package_id'), $packages);
            }

            foreach ($allPackages as $allPackage) {
                $value = [
                    'title' => null,
                    'type'  => null,
                    'value' => null,
                ];

                if (is_array($packages) && Arr::has($packages, $allPackage->entityId())) {
                    $type = Arr::get($packages, $allPackage->entityId() . '.type');

                    switch ($type) {
                        case Helper::COMPARISON_TYPE_YES:
                            $value = __p('core::phrase.yes');
                            break;
                        case Helper::COMPARISON_TYPE_NO:
                            $value = __p('core::phrase.no');
                            break;
                        default:
                            $value = Arr::get($packages, $allPackage->entityId() . '.value');
                            break;
                    }

                    $value = [
                        'title' => Helper::handleTitleForView(Arr::get($packages, $allPackage->entityId() . '.package_title')),
                        'type'  => $type,
                        'value' => $value,
                    ];
                }

                Arr::set($convertedPackages, $allPackage->entityId(), $value);
            }
        }

        return $convertedPackages;
    }
}
