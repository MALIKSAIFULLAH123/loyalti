<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use MetaFox\Subscription\Support\Browse\Traits\SubscriptionComparison\ExtraTrait;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class SubscriptionComparisonDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class SubscriptionComparisonDetail extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => Helper::MODULE_NAME,
            'resource_name' => Helper::COMPARISON_RESOURCE_ADMINCP_NAME,
            'title'         => $this->resource->toTitle(),
            'packages'      => $this->handlePackages(),
            'ordering'      => $this->resource->ordering,
            'extra'         => $this->getExtra(),
        ];
    }

    /**
     * @return array<int, mixed>
     * @throws AuthenticationException
     */
    protected function handlePackages(): array
    {
        $packages = $this->resource->packages;

        $context = user();

        $allPackages = SubscriptionPackage::getPackages($context, [
            'view' => Helper::VIEW_ADMINCP,
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
                            $value = 'yes';
                            break;
                        case Helper::COMPARISON_TYPE_NO:
                            $value = 'no';
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
