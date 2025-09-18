<?php

namespace MetaFox\Subscription\Observers;

use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class SubscriptionPackageObserver.
 */
class SubscriptionPackageObserver
{
    public function deleted(SubscriptionPackage $model)
    {
        Facade::handleAfterDeletingPackage($model);

        $model->subscriptions()->delete();

        $model->description()->delete();

        $model->comparisonData()->delete();
    }
}
