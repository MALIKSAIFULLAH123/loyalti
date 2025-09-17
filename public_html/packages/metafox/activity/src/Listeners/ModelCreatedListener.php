<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\PrivacyMember as ActivityPrivacyMember;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Support\Facades\ActivitySubscription;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\HasFeed;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsPrivacyMemberInterface;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\User\Models\UserBlocked;

/**
 * Class ModelCreatedListener.
 *
 * @ignore
 */
class ModelCreatedListener
{
    use CheckModeratorSettingTrait;

    /**
     * @param Model $model
     */
    public function handle($model): void
    {
        $this->handleAddSubscription($model);

        ActivityFeed::createFeedFromFeedSource($model);

        if ($model instanceof HasFeed) {
            LoadReduce::flush();
            $model->load('activity_feed');
        }

        // Check if the model is Core Privacy Member, clone to activity privacy member.
        if ($model instanceof IsPrivacyMemberInterface) {
            ActivityPrivacyMember::query()->firstOrCreate([
                'privacy_id' => $model->privacyId(),
                'user_id'    => $model->userId(),
            ]);
        }

        if ($model instanceof UserBlocked) {
            ActivitySubscription::deleteSubscription($model->userId(), $model->ownerId());
        }
    }

    protected function handleAddSubscription($model): void
    {
        if (!$model instanceof IsActivitySubscriptionInterface) {
            return;
        }

        $data = $model->toActivitySubscription();

        if (empty($data)) {
            return;
        }

        if (method_exists($model, 'shouldSubscribe') && !$model->shouldSubscribe()) {
            return;
        }

        ActivitySubscription::addSubscription(...$data);
    }
}
