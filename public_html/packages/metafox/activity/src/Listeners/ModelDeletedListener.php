<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\PrivacyMember as ActivityPrivacyMember;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Support\Facades\ActivitySubscription;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalFeed;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsPrivacyMemberInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class ModelDeletedListener.
 *
 * @ignore
 */
class ModelDeletedListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        $this->handleDeleteSubscription($model);

        if ($model instanceof ActivityFeedSource && $model instanceof Content) {
            $model->loadMissing('activity_feed');

            if ($model->activity_feed instanceof Feed) {
                $model->activity_feed->delete();
            }

            $owner = $model->owner;
            if ($owner instanceof HasTotalFeed) {
                $owner->decrementAmount('total_feed');
            }
        }

        // Check if model is Core Privacy Member, if deleted then also delete activity privacy member.
        if ($model instanceof IsPrivacyMemberInterface) {
            ActivityPrivacyMember::query()->where([
                'privacy_id' => $model->privacyId(),
                'user_id'    => $model->userId(),
            ])->delete();
        }

        //Only delete feeds of Link and Post because all other items which are belonged to User/Page/Group/Event will be deleted and its feed will also be deleted
        if ($model instanceof User) {
            ActivityFeed::deleteCoreFeedsByOwner($model->entityType(), $model->entityId());
            ActivitySchedule::query()->where([
                'owner_id'   => $model->entityId(),
                'owner_type' => $model->entityType(),
            ])->delete();
        }
    }

    protected function handleDeleteSubscription($model): void
    {
        if (!$model instanceof IsActivitySubscriptionInterface) {
            return;
        }

        $data = $model->toActivitySubscription();

        if (empty($data)) {
            return;
        }

        ActivitySubscription::deleteSubscription(...$data);
    }
}
