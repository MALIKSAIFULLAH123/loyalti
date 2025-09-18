<?php

namespace MetaFox\Subscription\Observers;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Subscription\Models\SubscriptionComparison;
use MetaFox\Subscription\Models\SubscriptionComparisonData;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class SubscriptionComparisonObserver.
 */
class SubscriptionComparisonObserver
{
    public function __construct(protected SubscriptionComparisonRepositoryInterface $repository)
    {
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof SubscriptionComparison) {
            $cacheIds = [];

            if (null !== $model->packages) {
                $query = (new SubscriptionComparisonData())->newModelQuery();

                foreach ($model->packages as $package) {
                    $cacheIds[] = Helper::getFeaturePackageCacheId($package->pivot->package_id);
                    $query->where([
                        'comparison_id' => $package->pivot->comparison_id,
                        'package_id'    => $package->pivot->package_id,
                    ])->delete();
                }
            }

            $this->repository->clearCaches($cacheIds);
        }
    }
}
