<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;

class CreateTaggedFriendFromResourceListener
{
    use HasFilterTagUserTrait;

    /**
     * @param  Model                $model
     * @param  array<string, mixed> $taggedFriends
     * @return Feed|null
     */
    public function handle(Model $model, array $taggedFriends = []): ?Feed
    {
        if (!$model instanceof ActivityFeedSource) {
            return null;
        }

        $feed = $model->activity_feed;

        if (!$feed instanceof Feed) {
            return null;
        }

        if (null === $feed->owner || null === $feed->user) {
            return null;
        }

        if (count($taggedFriends) > 0) {
            $extra = $this->transformTaggedFriends($feed->user, $feed->user, $feed->owner, $taggedFriends, $model->content);

            $taggedFriends = Arr::get($extra, 'tagged_friends');

            if (is_array($taggedFriends) && count($taggedFriends)) {
                app('events')->dispatch(
                    'friend.create_tag_friends',
                    [$feed->user, $feed->item, $taggedFriends, $feed->type_id],
                    true
                );
            }
        }

        return $feed;
    }
}
