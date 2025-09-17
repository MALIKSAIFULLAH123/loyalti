<?php

namespace MetaFox\Activity\Observers;

use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Snooze;
use MetaFox\Activity\Support\Facades\ActivitySubscription;
use MetaFox\Activity\Support\Support;
use MetaFox\Activity\Support\Facades\Snooze as SnoozeFacade;

class SnoozeObserver
{
    public function created(Snooze $model): void
    {
        ActivitySubscription::updateSubscription($model->userId(), $model->ownerId());

        $this->updateGlobalSubscription($model, false);

        SnoozeFacade::clearCache($model->userId());
    }

    public function updated(Snooze $model): void
    {
        $isActive = $this->determineSubscriptionStatus($model);

        ActivitySubscription::updateSubscription($model->userId(), $model->ownerId(), $isActive);

        $this->updateGlobalSubscription($model, $isActive);

        SnoozeFacade::clearCache($model->userId());
    }

    public function deleted(Snooze $model): void
    {
        ActivitySubscription::updateSubscription($model->userId(), $model->ownerId(), true);

        $this->updateGlobalSubscription($model, true);

        SnoozeFacade::clearCache($model->userId());
    }

    protected function updateGlobalSubscription(Snooze $model, bool $isActive): void
    {
        if (null === $model->owner) {
            return;
        }

        if (!$model->owner->hasSuperAdminRole()) {
            return;
        }

        ActivitySubscription::updateSubscription($model->userId(), $model->ownerId(), $isActive, Support::ACTIVITY_SUBSCRIPTION_VIEW_SUPER_ADMIN_FEED);
    }

    protected function determineSubscriptionStatus(Snooze $model): bool
    {
        if ($model->is_snooze_forever) {
            return false;
        }

        if (null === $model->snooze_until) {
            return false;
        }

        if ($model->snooze_until >= Carbon::now()) {
            return false;
        }

        return true;
    }
}
