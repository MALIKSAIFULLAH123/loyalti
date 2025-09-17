<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Models\Type;
use MetaFox\Activity\Policies\SharePolicy;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\ShareRepositoryInterface;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Support\Support;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * @method Share find($id, $columns = ['*'])
 * @method Share getModel()
 */
class ShareRepository extends AbstractRepository implements ShareRepositoryInterface
{
    public function model(): string
    {
        return Share::class;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @inheritdoc
     */
    public function share(User $context, User $owner, array $attributes): int
    {
        policy_authorize(SharePolicy::class, 'shareItem', $context, $owner, $attributes);

        $shareData = array_merge($attributes, [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
            'content'    => parse_output()->parse($attributes['content']),
        ]);

        $share = new Share();

        $share->fill($shareData);

        if (null == $share->item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        /**
         * Check share item on item policy if has.
         */
        $itemPolicy = PolicyGate::getPolicyFor(get_class($share->item));

        if (is_object($itemPolicy) && method_exists($itemPolicy, 'share')) {
            policy_authorize(get_class($itemPolicy), 'share', $context, $share->item);
        }

        if ($attributes['parent_feed_id'] > 0) {
            $feed = $this->feedRepository()->find($attributes['parent_feed_id']);

            if (null != $feed) {
                $itemFeed = $feed->item;
                if (!$itemFeed instanceof Share) {
                    if ($feed->itemType() != $share->itemType()) {
                        throw (new ModelNotFoundException())->setModel(Feed::class);
                    }

                    if ($feed->itemId() != $share->itemId()) {
                        throw (new ModelNotFoundException())->setModel(Feed::class);
                    }
                }

                if ($itemFeed instanceof Share) {
                    if ($itemFeed->itemType() != $share->itemType()) {
                        throw (new ModelNotFoundException())->setModel(Feed::class);
                    }

                    if ($itemFeed->itemId() != $share->itemId()) {
                        throw (new ModelNotFoundException())->setModel(Feed::class);
                    }
                }
            }
        }

        if (Support::SHARED_TYPE !== $attributes['post_type']) {
            $privacy = UserPrivacy::getItemPrivacySetting($owner->entityId(), 'feed.item_privacy');

            if ($owner->entityId() != $context->entityId()) {
                $privacy = UserPrivacy::getProfileSetting($owner->entityId(), 'feed:view_wall');
            }

            Arr::set($shareData, 'privacy', false !== $privacy ? $privacy : MetaFoxPrivacy::EVERYONE);
        }

        $shareData = $this->resolveItemType($shareData);

        $share = new Share($shareData);

        $share = $this->saveShare($share, $attributes);

        if (!empty($attributes['tagged_friends'])) {
            app('events')->dispatch(
                'friend.create_tag_friends',
                [$context, $share, $attributes['tagged_friends'], null],
                true
            );
        }

        /** @var Feed $feed */
        $feed = ActivityFeed::getFeedByShareId($share->entityId());

        if ($feed instanceof HasHashTag) {
            app('events')->dispatch('hashtag.create_hashtag', [$context, $feed, $feed->content], true);
        }

        if (!$feed instanceof Feed) {
            return 0;
        }

        $feed->update(['from_resource' => Feed::FROM_FEED_RESOURCE]);

        ActivityFeed::sendFeedComposeNotification($feed);

        return $feed->entityId();
    }

    private function resolveItemType(array $sharedData): array
    {
        $applyNewRule = !MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.6', '>=');

        if ($applyNewRule) {
            $contextItemType = Arr::get($sharedData, 'context_item_type');
            $contextItemId = Arr::get($sharedData, 'context_item_id');
            $itemType = Arr::get($sharedData, 'item_type');
            $itemId = Arr::get($sharedData, 'item_id');
            $columns = ['context_item_type', 'context_item_id'];

            if (!$contextItemType || !$contextItemId) {
                Arr::forget($sharedData, $columns);
                return $sharedData;
            }

            if ($contextItemType == $itemType && $contextItemId == $itemId) {
                Arr::forget($sharedData, $columns);
                return $sharedData;
            }

            if ($contextItemType != Feed::ENTITY_TYPE) {
                return $sharedData;
            }

            $feed = (new Share($sharedData))->context_item;

            if (!$feed instanceof Feed) {
                Arr::forget($sharedData, $columns);
                return $sharedData;
            }

            $item = $feed->item;

            if ($item->entityId() != $itemId || $item->entityType() != $itemType) {
                return $sharedData;
            }

            Arr::forget($sharedData, $columns);

            return $sharedData;
        }

        if (Arr::get($sharedData, 'item_type') != Feed::ENTITY_TYPE) {
            return $sharedData;
        }

        $feed = (new Share($sharedData))->item;

        if (!$feed instanceof Feed) {
            return $sharedData;
        }

        $item = $feed->item;

        if ($item instanceof Post) {
            return $sharedData;
        }

        if (!$item instanceof Share) {
            /**
             * In case if share a feed of thread, total_share of thread and total_share on feed is separate
             */
            $actionOnFeed =  resolve(TypeManager::class)->hasFeature($feed->type_id, Type::ACTION_ON_FEED_TYPE);

            $data = [
                'item_type' => $item->entityType(),
                'item_id'   => $item->entityId(),
            ];

            if ($actionOnFeed) {
                $data = array_merge($data, [
                    'context_item_type' => $feed->entityType(),
                    'context_item_id' => $feed->entityId(),
                ]);
            }

            return array_merge($sharedData, $data);
        }

        $item = $item->item;

        if (!$item instanceof HasTotalShare) {
            return $sharedData;
        }

        return array_merge($sharedData, [
            'item_type' => $item->entityType(),
            'item_id'   => $item->entityId(),
            'context_item_type' => $feed->entityType(),
            'context_item_id' => $feed->entityId(),
        ]);
    }

    /**
     * @return FeedRepositoryInterface
     */
    private function feedRepository(): FeedRepositoryInterface
    {
        return resolve(FeedRepositoryInterface::class);
    }

    /**
     * @param Share                $share
     * @param array<string, mixed> $attributes
     *
     * @return Share
     */
    private function saveShare(Share $share, array $attributes): Share
    {
        if ($share->privacy == MetaFoxPrivacy::CUSTOM) {
            $share->setPrivacyListAttribute($attributes['list']);
        }

        $share->save();

        return $share->refresh();
    }

    /**
     * @param Share                $share
     * @param array<string, mixed> $attributes
     *
     * @return Share
     */
    public function updateShare(Share $share, array $attributes): Share
    {
        $share->fill($attributes);

        return $this->saveShare($share, $attributes);
    }

    public function deleteUserData(int $userId): void
    {
        $shares = $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        foreach ($shares as $share) {
            $share->delete();
        }
    }

    public function deleteOwnerData(int $ownerId): void
    {
        $shares = $this->getModel()->newModelQuery()
            ->where([
                'owner_id' => $ownerId,
            ])
            ->get();

        foreach ($shares as $share) {
            $share->delete();
        }
    }
}
