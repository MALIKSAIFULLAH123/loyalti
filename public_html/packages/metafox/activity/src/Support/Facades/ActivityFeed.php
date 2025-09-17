<?php

/** @noinspection ALL */

namespace MetaFox\Activity\Support\Facades;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Facade;
use MetaFox\Activity\Contracts\ActivityFeedContract;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Post;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\FeedAction;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityFeed.
 * @method static Feed                    createActivityFeed(FeedAction $feedAction)
 * @method static bool                    deleteActivityFeed(int $feedId)
 * @method static Post                    createActivityPost($content, $privacy, $user, $owner = null, $list = [], $relations = [])
 * @method static void                    putToStream(Feed $feed, array $attributes = [])
 * @method static void                    putToTagStream(Feed $feed, User $context, int $userAutoTag, array $attributes = [])
 * @method static array|null              getBackgroundStatusImage(int $bgStatusId)
 * @method static JsonResource|array|null getBackgroundStatus(int $bgStatusId)
 * @method static bool                    sendFeedComposeNotification(Feed $feed)
 * @method static void                    deleteTagsStream(array $conditions)
 * @method static array|null              getPrivacyDetail(User $context, Content $feed, ?int $representativePrivacy = null)
 * @method static Feed|null               createFeedFromFeedSource(Model $model, ?string $fromResource = Feed::FROM_APP_RESOURCE)
 * @mixin \MetaFox\Activity\Support\ActivityFeed
 */
class ActivityFeed extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActivityFeedContract::class;
    }
}
