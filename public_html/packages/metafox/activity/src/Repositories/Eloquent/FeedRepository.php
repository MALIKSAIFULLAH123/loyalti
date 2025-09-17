<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use MetaFox\Activity\Contracts\ActivityHiddenManager;
use MetaFox\Activity\Contracts\ActivityPinManager;
use MetaFox\Activity\Models\ActivityHistory as History;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Pin;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Stream;
use MetaFox\Activity\Notifications\PendingFeedNotification;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Repositories\ActivityHistoryRepositoryInterface;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\PinRepositoryInterface;
use MetaFox\Activity\Repositories\ShareRepositoryInterface;
use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Activity\Support\Contracts\StreamManagerInterface;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Support\PinPostManager;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasFeedContent;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Repositories\UserPreferenceRepositoryInterface;
use MetaFox\User\Support\CacheManager;
use MetaFox\User\Support\Facades\User as UserFacades;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User as UserSupport;

/**
 * Class FeedRepository.
 *
 * @property Feed $model
 * @method   Feed find($id, $columns = ['*'])
 * @method   Feed getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class FeedRepository extends AbstractRepository implements FeedRepositoryInterface
{
    use IsFriendTrait {
        IsFriendTrait::getTaggedFriends as getTaggedFriendsTrait;
    }

    use HasSponsor;

    protected ActivityHistoryRepositoryInterface $historyRepository;

    public function __construct(Application $app, ActivityHistoryRepositoryInterface $historyRepository)
    {
        parent::__construct($app);
        $this->historyRepository = $historyRepository;
    }

    public function model(): string
    {
        return Feed::class;
    }

    /**
     * @inherhitDoc
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function getFeeds(
        User    $user,
        ?User   $owner = null,
        ?int    $lastFeedId = null,
        int     $need = Pagination::DEFAULT_ITEM_PER_PAGE,
        ?string $hashtag = null,
        bool    $friendOnly = false,
        ?array  $extraConditions = null,
        ?string $sort = Browse::SORT_RECENT,
        ?string $sortType = MetaFoxConstant::SORT_DESC,
        bool    $getFeedSponsor = false,
        ?array  $status = null,
        bool    $isPreviewTag = false,
        ?array  $loadedSponsoredFeedIds = null,
        array   $params = [],
    )
    {
        $collection  = new Collection();
        $hasFetchPin = (bool) Arr::get($params, 'has_pin_post', true);

        $streamManager = $this->getStreamManager();

        $streamManager->setUser($user)
            ->setStatus($status)
            ->setPreviewTag($isPreviewTag);

        if ($owner) {
            $streamManager->setOwnerId($owner->entityId());
        }

        if (null !== $hashtag) {
            $isHashtag = Str::startsWith($hashtag, '#');

            switch ($isHashtag) {
                case true:
                    $streamManager->setIsViewHashtag(true);

                    if ('' !== $hashtag) {
                        $streamManager->setHashtag($hashtag);
                    }

                    break;
                default:
                    $streamManager->setIsViewSearch(true);

                    if ('' !== $hashtag) {
                        $streamManager->setSearchString($hashtag);
                    }

                    break;
            }
        }

        if (!empty($extraConditions)) {
            $streamManager->setAdditionalConditions($extraConditions);
        }

        $feedPolicy = resolve('FeedPolicySingleton');

        if (!$feedPolicy->viewAny($user, $owner)) {
            throw new AuthorizationException();
        }

        if (null === $sort) {
            $sort = Browse::SORT_RECENT;
        }

        if (null === $sortType) {
            $sortType = MetaFoxConstant::SORT_DESC;
        }

        $fetchSponsoredFeed = $getFeedSponsor === true && !$streamManager->isViewOnProfile() && null === $lastFeedId;

        $streamManager->setLimit($need)
            ->setSortFields($sort, $sortType)
            ->setOnlyFriends($friendOnly);

        //@todo Does the same with sponsor feed
        $allowGetPinnedFeeds = !$isPreviewTag && $hasFetchPin;

        if ($allowGetPinnedFeeds) {
            $streamManager->fetchPinnedFeeds();
        }

        if (null == $loadedSponsoredFeedIds) {
            $loadedSponsoredFeedIds = [];
        }

        if ($fetchSponsoredFeed) {
            $sponsorFeedIds = $streamManager->fetchSponsoredFeeds($loadedSponsoredFeedIds);

            request()->request->set('current_sponsored_feed_ids', $sponsorFeedIds);

            $loadedSponsoredFeedIds = array_unique(array_merge($loadedSponsoredFeedIds, $sponsorFeedIds));
        }

        request()->request->set('pagination_sponsored_feed_ids', array_map(function ($id) {
            return (int) $id;
        }, $loadedSponsoredFeedIds));

        $streamManager
            ->setLoadedSponsoredFeedIds($loadedSponsoredFeedIds)
            ->fetchStreamContinuous($collection, $need, $lastFeedId, 0);

        $collection = $collection->slice(0, $need);

        if ($fetchSponsoredFeed) {
            $streamManager->addSponsoredFeed($collection);
        }

        if ($allowGetPinnedFeeds && !$lastFeedId) {
            $streamManager->addPinnedFeedIds($collection);
        }

        $feedIds = $collection->toArray();

        $feeds = $streamManager->toFeeds($feedIds);

        if ($allowGetPinnedFeeds && !$lastFeedId) {
            $feeds = $this->filterPinnedFeedPrivacy($user, $feeds, $streamManager->getPinnedFeedIds());
        }

        if ($streamManager->isViewOnProfile()) {
            request()->request->add([
                'is_profile_feed' => true,
            ]);
        }

        return $feeds;
    }

    private function filterPinnedFeedPrivacy(User $user, Collection $feeds, array $pinnedFeedIds): Collection
    {
        if (!count($pinnedFeedIds)) {
            return $feeds;
        }

        $feeds = $feeds->filter(function ($feed) use ($user, $pinnedFeedIds) {
            if (!in_array($feed->entityId(), $pinnedFeedIds)) {
                return true;
            }

            if (!policy_check(FeedPolicy::class, 'view', $user, $feed)) {
                return false;
            }

            return true;
        });

        return $feeds->values();
    }

    public function getFeed(?User $user, int $id): Feed
    {
        $resource = $this->find($id);

        policy_authorize(FeedPolicy::class, 'view', $user, $resource);

        return $resource;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws AuthorizationException
     * @inheritDoc
     */
    public function createFeed(User $context, User $user, User $owner, array $params): array
    {
        $postType = Arr::get($params, 'post_type');

        $this->checkCreateFeedPolicy($context, $user, $owner, $params);

        $response = app('events')->dispatch('feed.composer', [$user, $owner, $postType, $params], true);

        if (!is_array($response)) {
            abort(400, __p('activity::phrase.feed_cannot_be_created'));
        }

        $isSpecialCase = false;

        if (Arr::get($response, 'is_processing', false)) {
            $isSpecialCase = true;
        }

        if (Arr::get($response, 'is_pending', false)) {
            $isSpecialCase = true;
        }

        if ($isSpecialCase) {
            return $response;
        }

        $feedId = (int) Arr::get($response, 'id', 0);

        if ($feedId < 1) {
            $errorMessage = Arr::get($response, 'error_message', __p('activity::phrase.feed_cannot_be_created'));

            $errorCode = Arr::get($response, 'error_code', 400);

            abort($errorCode, $errorMessage);
        }

        $feed = $this->find($feedId)->load('item');

        $update       = ['from_resource' => Feed::FROM_FEED_RESOURCE];
        $scheduleTime = Arr::get($params, 'schedule_time');
        if ($scheduleTime) {
            $update['created_at'] = $scheduleTime;
            $update['updated_at'] = $scheduleTime;
        }

        $feed->update($update);

        $taggedFriends = Arr::get($params, 'tagged_friends');

        if (is_array($taggedFriends)) {
            app('events')->dispatch(
                'friend.create_tag_friends',
                [$user, $feed->item, $taggedFriends, $feed->type_id],
                true
            );
        }

        app('events')->dispatch('hashtag.create_hashtag', [$context, $feed, $feed->content], true);

        ActivityFeed::sendFeedComposeNotification($feed);

        if (!$feed->isApproved()) {
            return ['feed' => $feed, 'message' => __p('activity::phrase.thanks_for_your_post_for_approval')];
        }

        return ['feed' => $feed, 'message' => __p('activity::phrase.feed_created_successfully')];
    }

    /**
     * @param User                $context
     * @param User                $user
     * @param int                 $id
     * @param array<string,mixed> $params
     *
     * @return Feed
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @todo: need to clean up the process of update feed and its item? (too much dependency)
     */
    public function updateFeed(User $context, User $user, int $id, array $params): Feed
    {
        $feed = $this->with(['user', 'userEntity', 'owner', 'ownerEntity', 'item'])->find($id);

        $owner = $feed->owner;

        $oldActivityHistory = null;

        policy_authorize(FeedPolicy::class, 'update', $context, $feed);

        app('events')->dispatch('feed.pre_composer_edit', [$context, $params], true);
        $postType = $params['post_type'];
        unset($params['post_type']);

        $content = trim(Arr::get($params, 'content', ''));

        $hasUpdateContent = $feed->content != $content;

        if ($hasUpdateContent) {
            app('events')->dispatch('translation.clear_translated_text', [$feed], true);
        }

        /**
         * It is flag for item to response phrase for first history.
         */
        $isFirstHistory = false;
        $isExists       = $this->historyRepository->checkExists($feed->entityId());

        if (!$isExists) {
            $oldActivityHistory = $this->handleCreateHistory($feed->user, $feed, $hasUpdateContent, $postType);
        }

        if (null !== $oldActivityHistory) {
            $isFirstHistory = true;
        }
        Arr::set($params, 'is_first_history', $isFirstHistory);

        $checkSpam = resolve(FeedRepositoryInterface::class)
            ->checkSpamStatus($user, $feed->itemType(), $content, $feed->itemId());

        if ('' !== $content && $checkSpam) {
            abort(400, __p('core::phrase.you_have_already_added_this_recently_try_adding_something_else'));
        }

        $newHashTag = $oldHashTag = '';

        if ($feed->item instanceof HasFeedContent) {
            $oldContent = $feed->item->getFeedContent();
            $tags       = parse_output()->getHashtags($oldContent);
            if (count($tags)) {
                $oldHashTag = implode(',', $tags);
            }
        }

        //In case user want to change content for approval again
        if ($owner->hasPendingMode() && $feed->is_denied && $hasUpdateContent) {
            $feed->update(['status' => MetaFoxConstant::ITEM_STATUS_PENDING]);
            app('events')->dispatch('models.notify.pending', [$feed], true);
        }

        $response = app('events')->dispatch('feed.composer.edit', [$user, $owner, $feed->item, $params], true);

        $success = Arr::get($response, 'success', false);
        $phrase  = Arr::get($response, 'phrase', []);
        $extra   = Arr::get($response, 'extra', false);

        if (!$success) {
            $errorMessage = Arr::get($response, 'error_message', __('validation.invalid'));

            $errorCode = Arr::get($response, 'error_code', 400);

            abort($errorCode, $errorMessage);
        }

        // Refresh to get updated item.
        $feed->refresh();

        $isPhrase = Arr::get($phrase, 'new') != null;

        $newActivityHistory = $this->handleCreateHistory($context, $feed, $hasUpdateContent, $postType, $isPhrase);

        if ($phrase) {
            if ($oldActivityHistory != null) {
                $attributes = ['phrase' => $phrase['old'], 'extra' => $extra['old']];

                $this->historyRepository->updateHistory($oldActivityHistory, $attributes);
            }

            if (null === $phrase['new'] && $hasUpdateContent) {
                if (null === $content || MetaFoxConstant::EMPTY_STRING === $content) {
                    Arr::set($phrase, 'new', 'no_content');
                }
            }

            $attributes = ['phrase' => $phrase['new'], 'extra' => $extra['new']];
            if ($newActivityHistory != null) {
                $this->historyRepository->updateHistory($newActivityHistory, $attributes);
            }
        }

        /** @var Content $feedItem */
        $feedItem = $feed->item;

        $feedItem->refresh();

        $content = $feed->content;

        $tags = parse_output()->getHashtags($content);

        if (count($tags)) {
            $newHashTag = implode(',', $tags);
        }

        if (Arr::has($params, 'tagged_friends')) {
            app('events')->dispatch(
                'friend.update_tag_friends',
                [$feed->user, $feedItem, $params['tagged_friends'], $feed->type_id],
                true
            );
        }

        if ('' !== $newHashTag && $newHashTag != $oldHashTag) {
            app('events')->dispatch('hashtag.create_hashtag', [$context, $feed, $content], true);
        }

        if ('' === $newHashTag && '' !== $oldHashTag) {
            $feed->tagData()->sync([]);
        }

        return $feed;
    }

    /**
     * @param User   $user
     * @param Feed   $feed
     * @param bool   $hasUpdateContent
     * @param string $postType
     * @param bool   $isPhrase
     *
     * @return History|null
     */
    protected function handleCreateHistory(
        User   $user,
        Feed   $feed,
        bool   $hasUpdateContent,
        string $postType,
        bool   $isPhrase = false
    ): ?History
    {
        if ($hasUpdateContent) {
            return $this->historyRepository->createHistory($user, $feed);
        }

        if ($postType != Post::ENTITY_TYPE) {
            $isExists = $this->historyRepository->checkExists($feed->entityId());

            if (!$isExists) {
                return $this->historyRepository->createHistory($feed->user, $feed);
            }

            return $this->historyRepository->createHistory($user, $feed);
        }

        return null;
    }

    /**
     * Update feed privacy.
     *
     * @param User                $context
     * @param Feed                $feed
     * @param array<string,mixed> $params
     *
     * @return Feed
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function updateFeedPrivacy(User $context, Feed $feed, array $params): Feed
    {
        $item = $feed->item;
        if (null == $item) {
            return $feed;
        }

        $privacy = Arr::get($params, 'privacy');

        $privacyList = Arr::get($params, 'list');

        if (!$item instanceof HasPrivacy) {
            if (!$context->can('updatePrivacy', [$feed, $privacy])) {
                throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
            }
            $feed->privacy = $privacy;

            $feed->setPrivacyListAttribute($privacyList);

            $feed->save();

            return $feed->refresh();
        }

        if (!$context->can('updatePrivacy', [$item, $privacy])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        app('events')->dispatch('activity.update_feed_item_privacy', [
            $item->entityId(),
            $item->entityType(),
            $privacy,
            $privacyList,
        ]);

        // Refresh to get updated item.
        $feed->refresh();

        return $feed;
    }

    public function deleteFeed(User $user, int $id): bool
    {
        $resource = $this->find($id);

        policy_authorize(FeedPolicy::class, 'delete', $user, $resource);

        if (!$this->delete($id)) {
            return false;
        }

        if ($resource->from_resource == Feed::FROM_FEED_RESOURCE) {
            $this->deleteRelatedItems($resource);
        }

        return true;
    }

    public function deleteFeedWithItems(User $user, int $id): bool
    {
        $resource = $this->find($id);

        policy_authorize(FeedPolicy::class, 'deleteWithItems', $user, $resource);

        if (!$this->delete($id)) {
            return false;
        }

        $this->deleteRelatedItems($resource);

        return true;
    }

    public function hideFeed(User $user, Feed $feed): bool
    {
        policy_authorize(FeedPolicy::class, 'hideFeed', $user, $feed);

        $service = resolve(ActivityHiddenManager::class);

        if (null == $feed->item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        $data = $feed->hiddenFeeds()->sync([
            $user->entityId() => [
                'user_type' => $user->entityType(),
            ],
        ], false);

        $service->clearCache($user->entityId());

        return in_array($user->entityId(), $data['attached']);
    }

    public function unHideFeed(User $user, Feed $feed): bool
    {
        policy_authorize(FeedPolicy::class, 'unHideFeed', $user, $feed);

        $service = resolve(ActivityHiddenManager::class);

        $response = $feed->hiddenFeeds()->detach($user->entityId());

        $service->clearCache($user->entityId());

        return (bool) $response;
    }

    private function shareRepository(): ShareRepositoryInterface
    {
        return resolve(ShareRepositoryInterface::class);
    }

    public function getFeedForEdit(User $context, int $id): Feed
    {
        $feed = $this->with('item')->find($id);

        policy_authorize(FeedPolicy::class, 'update', $context, $feed);

        return $feed;
    }

    public function getFeedByItem(?User $context, ?Entity $content, ?string $typeId = null): Feed
    {
        if ($typeId === null) {
            $typeId = $content->entityType();
        }

        /** @var Feed $feed */
        $feed = $this->getModel()->newModelInstance()
            ->with('item')
            ->where([
                'item_id'   => $content->entityId(),
                'item_type' => $content->entityType(),
                'type_id'   => $typeId,
            ])->firstOrFail();

        policy_authorize(FeedPolicy::class, 'view', $context, $feed);

        return $feed;
    }

    public function getTaggedFriends(int $itemId, string $itemType, int $limit, array $excludedIds = []): LengthAwarePaginator
    {
        /** @var Content $item */
        $item = (new Feed([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ]))->item;

        if (null == $item) {
            throw (new ModelNotFoundException())->setModel($itemType);
        }

        $taggedFriendQuery = $this->getTaggedFriendsTrait($item, $limit, $excludedIds);

        if (!$taggedFriendQuery instanceof Builder) {
            return new Paginator([], 0, $limit);
        }

        return $taggedFriendQuery->paginate($limit, ['user_entities.*']);
    }

    public function getSpamStatusSetting(): int
    {
        return Settings::get('activity.feed.spam_check_status_updates', 0);
    }

    public function checkSpamStatus(User $user, string $itemType, ?string $content, ?int $itemId = null): bool
    {
        $limit = $this->getSpamStatusSetting();
        // issuer performance

        if ($limit == 0) {
            return false;
        }

        $query = $this->getModel()->newQuery()
            ->where('user_id', '=', $user->entityId())
            ->where('user_type', '=', $user->entityType())
            ->where('item_type', '=', $itemType)
            ->orderBy('updated_at', 'DESC')
            ->limit($limit);

        if ($itemId !== null) {
            $query->where('item_id', '!=', $itemId);
        }
        /** @var array<Feed> $feeds */
        $feeds = $query->get();
        foreach ($feeds as $feed) {
            if ($content == $feed->content) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $context
     * @param Feed $feed
     * @param int  $sponsor
     *
     * @return bool
     * @throws AuthorizationException|AuthorizationException
     */
    public function sponsorFeed(User $context, Feed $feed, int $sponsor): bool
    {
        policy_authorize(FeedPolicy::class, 'sponsor', $context, $feed, $sponsor);

        return $feed->update(['is_sponsor' => $sponsor]);
    }

    public function getFeedByItemId(
        User   $context,
        int    $itemId,
        string $itemType,
        string $typeId,
        bool   $checkPermission = true
    ): ?Feed
    {
        /** @var Feed $feed */
        $query = $this->getModel()->newModelInstance()
            ->with('item')
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
                'type_id'   => $typeId,
            ]);

        if (!$checkPermission) {
            return $query->first();
        }

        /** @var Feed $feed */
        $feed = $query->firstOrFail();

        policy_authorize(FeedPolicy::class, 'view', $context, $feed);

        return $feed;
    }

    /**
     * @param int $feedId
     *
     * @return bool
     * @todo: need to rework to implement mechanism
     */
    public function pushFeedOnTop(int $feedId): bool
    {
        $feed = $this->find($feedId);

        return $feed->update(['updated_at' => Carbon::now()]);
    }

    private function getStreamManager(): StreamManagerInterface
    {
        return resolve(StreamManagerInterface::class);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return array<int, mixed>      the returned array should be as format array($data, $extra, $message)
     * @throws AuthorizationException
     */
    public function approvePendingFeed(User $context, int $id): array
    {
        $feed = $this->with(['item', 'user', 'owner'])->find($id);

        $item                    = $feed->item;
        $user                    = $feed->user;
        $owner                   = $feed->owner;
        $notificationPendingType = resolve(PendingFeedNotification::class)->getType();

        policy_authorize(FeedPolicy::class, 'viewContent', $context, $owner, $feed->status);

        $feed->update(['status' => MetaFoxConstant::ITEM_STATUS_APPROVED]);

        $item->update(['is_approved' => true]);

        app('events')->dispatch('models.notify.approved', [$context, $feed], true);
        app('events')->dispatch('activity.notify.approved_new_post_in_owner', [$feed, $owner], true);
        app('events')->dispatch('friend.notify.publish_tag_in_owner', [$item], true);
        $this->handleRemoveNotification($notificationPendingType, $feed->entityId(), $feed->entityType());

        $returnData = [
            'id' => $feed->entityId(),
        ];
        $message    = __p('activity::phrase.user_post_approved', ['user' => $user?->full_name ?? '']);

        return [$returnData, [], $message];
    }

    public function declinePendingFeed(User $context, int $id, array $params): bool
    {
        $relations     = ['item', 'owner'];
        $isBlockAuthor = Arr::get($params, 'is_block_author', false);

        if ($isBlockAuthor) {
            $relations[] = 'user';
        }

        $feed                    = $this->with($relations)->find($id);
        $owner                   = $feed->owner;
        $notificationPendingType = resolve(PendingFeedNotification::class)->getType();

        policy_authorize(FeedPolicy::class, 'viewContent', $context, $owner, $feed->status);

        if (!$isBlockAuthor) {
            $this->handleRemoveNotification($notificationPendingType, $feed->entityId(), $feed->entityType());

            return $feed->update(['status' => MetaFoxConstant::ITEM_STATUS_DENIED]);
        }

        $blockedUser = $feed->user;

        $result = app('events')->dispatch('activity.delete_feed', [$feed], true);

        if (!$result) {
            return false;
        }

        if ($blockedUser instanceof User) {
            app('events')->dispatch('activity.feed.block_author', [$context, $owner, $blockedUser, $params], true);
        }

        return true;
    }

    public function countFeedPendingOnOwner(User $context, User $owner): int
    {
        $feedPolicy = resolve('FeedPolicySingleton');
        if (!$feedPolicy->viewAny($context, $owner)) {
            return 0;
        }

        return $this->getModel()->newQuery()
            ->where('user_id', $context->entityId())
            ->where('owner_id', $owner->entityId())
            ->where('status', '=', MetaFoxConstant::ITEM_STATUS_PENDING)
            ->count();
    }

    public function pinFeed(User $context, ?User $owner, int $feedId): bool
    {
        $feed = $this->find($feedId);

        policy_authorize(FeedPolicy::class, 'pinFeed', $context, $feed);

        $service = resolve(ActivityHiddenManager::class);

        if (null == $feed->item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        $data = $feed->pinnedFeeds()->sync([
            $context->entityId() => [
                'user_type' => $context->entityType(),
            ],
        ], false);

        $service->clearCache($context->entityId());

        return in_array($context->entityId(), $data['attached']);
    }

    public function unPinFeed(User $context, ?User $owner, int $id): bool
    {
        $feed = $this->find($id);

        policy_authorize(FeedPolicy::class, 'unPinFeed', $context, $feed);

        $service = resolve(ActivityPinManager::class);

        $response = $feed->pinnedFeeds()->detach($context->entityId());

        $service->clearCache($context->entityId());

        return (bool) $response;
    }

    public function getPinnedFeedIds(User $context): array
    {
        return Pin::query()->where([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ])->get()->pluck('id')->toArray();
    }

    public function countPinnedFeeds(User $context): int
    {
        return Pin::query()->where([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ])->count();
    }

    public function getPinnedFeeds(User $user, User $owner): Collection
    {
        $streamManager = resolve(PinPostManager::class);
        $streamManager->setUserId($user->entityId());
        $streamManager->setOwnerId($owner->entityId());

        $feedPolicy = resolve('FeedPolicySingleton');
        if (!$feedPolicy->viewAny($user, $owner)) {
            throw new AuthorizationException();
        }

        $sort     = Browse::SORT_RECENT;
        $sortType = MetaFoxConstant::SORT_DESC;

        $streamManager->setSortFields($sort, $sortType);

        $collection = $streamManager->fetchStream();

        $feedIds = $collection->pluck('feed_id')->toArray();

        $feeds = $streamManager->toFeeds($feedIds);

        request()->request->add([
            'is_profile_feed' => true,
        ]);

        return $feeds;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function removeTagFriend(Feed $feed): bool
    {
        policy_authorize(FeedPolicy::class, 'removeTag', $feed);
        $context = user();

        if (null == $feed->item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        app('events')->dispatch('friend.delete_mention_and_tag_friend', [$feed->item, $context], true);

        $this->handleUpdateContent($context, $feed->item);

        $feed->refresh();

        return true;
    }

    /**
     * This method handles updating the content of a given item by processing any mention patterns.
     *
     * @param User         $context The user performing the update.
     * @param Content|null $item    The content item to update.
     *
     * @return void
     */
    protected function handleUpdateContent($context, ?Content $item): void
    {
        if (!Schema::hasColumn($item->getTable(), 'content')) {
            return;
        }

        $content = $item->content;

        $patterns = $this->getMentionPatterns();
        if (empty($patterns)) {
            return;
        }

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);

            [$match, $userIds, $names] = $matches;

            if (empty($userIds)) {
                continue;
            }

            for ($i = 0; $i < count($userIds); $i++) {
                if ($userIds[$i] == $context->entityId()) {
                    $content = Str::replace($match[$i], $names[$i], $content);
                }
            }
        }

        $item->content = $content;

        $item->save();
    }

    protected function getMentionPatterns(): array
    {
        $patterns = app('events')->dispatch('core.mention.pattern');
        $patterns = array_filter($patterns);

        return array_values(Arr::dot($patterns));
    }

    /**
     * @param User  $context
     * @param array $params
     *
     * @return bool
     */
    public function allowReviewTag(User $context, Feed $feed, array $params): bool
    {
        $conditions = [
            'feed_id'  => $feed->entityId(),
            'user_id'  => $feed->userId(),
            'owner_id' => $context->entityId(),
        ];

        $tags = $this->getTaggedFriend($feed->item, $context);

        if (!empty($tags)) {
            policy_authorize(FeedPolicy::class, 'removeTag', $feed);
        }

        if ($params['is_allowed'] == Stream::STATUS_ALLOW) {
            $stream = Stream::query()->where($conditions)->first();

            if (!$stream) { // prevent crashed.
                return false;
            }

            return $stream->update([
                'status' => 0,
            ]);
        }

        return Stream::query()->where($conditions)->delete();
    }

    /**
     * @param string $typeId
     *
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function handlePutToTagStream(User $context, User $friend, int $itemId, string $itemType, string $typeId, array $attributes = [])
    {
        $isAllowTaggerPost = (int) UserValue::checkUserValueSettingByName($friend, 'user_auto_add_tagger_post');
        $itemId            = Arr::get($attributes, 'item_id', $itemId);
        $itemType          = Arr::get($attributes, 'item_type', $itemType);
        $typeId            = Arr::get($attributes, 'type_id', $typeId);

        $feed = $this->getFeedByItemId($context, $itemId, $itemType, $typeId, false);

        if (!$feed instanceof Feed) {
            return;
        }

        ActivityFeed::putToTagStream($feed, $friend, $isAllowTaggerPost, $attributes);
    }

    public function getPrivacyDetail(User $context, Content $resource, ?int $representativePrivacy = null): array
    {
        return $this->handlePrivacyDetail($context, $resource, $resource->owner, $representativePrivacy);
    }

    public function getOwnerPrivacyDetail(User $context, User $resource, ?int $representativePrivacy = null): array
    {
        return $this->handlePrivacyDetail($context, $resource, $resource, $representativePrivacy);
    }

    protected function handlePrivacyDetail(User $context, Content $resource, ?User $owner, ?int $representativePrivacy = null): array
    {
        $privacy = $representativePrivacy ?? $resource->privacy ?? MetaFoxPrivacy::EVERYONE;

        // In case we can not find the owner's privacy
        if (null === $owner) {
            return $this->getDefaultPrivacyDetail($privacy);
        }

        // In case some model want to control icon + tooltip for Everyone and Friends of Friends privacy
        $representativePrivacyDetail = $owner->getRepresentativePrivacyDetail($privacy);

        if (null !== $representativePrivacyDetail) {
            return $representativePrivacyDetail;
        }

        if (in_array($privacy, [MetaFoxPrivacy::EVERYONE, MetaFoxPrivacy::FRIENDS_OF_FRIENDS])) {
            return $this->getDefaultPrivacyDetail($privacy, $context, $owner);
        }

        $icons = $this->collectIcons();

        // In case no apps listening this event
        if (!count($icons)) {
            return $this->getDefaultPrivacyDetail($privacy, $context, $owner);
        }

        $privacyType = resolve(PrivacyRepositoryInterface::class)->getPrivacyTypeByPrivacy(
            $owner->entityId(),
            $privacy
        );

        if (null === $privacyType) {
            return $this->getDefaultPrivacyDetail($privacy, $context, $owner);
        }

        $detail = Arr::get($icons, $privacyType);

        if (null === $detail) {
            return $this->getDefaultPrivacyDetail($privacy, $context, $owner);
        }

        $object = [
            'privacy_icon' => Arr::get($detail, 'privacy'),
        ];

        $tooltip = Arr::get($detail, 'privacy_tooltip');

        if (is_array($tooltip)) {
            $tooltipParams = Arr::get($tooltip, 'params');

            $phraseParams = [];

            if (is_array($tooltipParams)) {
                foreach ($tooltipParams as $name => $value) {
                    $relation = $resource;

                    if (is_string($value)) {
                        $relation = $relation->{$value};
                    }

                    if (null !== $relation) {
                        Arr::set($phraseParams, $name, $relation->{$name});
                    }
                }
            }

            Arr::set($object, 'tooltip', __p(Arr::get($tooltip, 'var_name'), $phraseParams));
        }

        return $object;
    }

    protected function getDefaultPrivacyDetail(int $privacy, ?User $context = null, ?User $owner = null): array
    {
        $privacyDetail = app('events')->dispatch('core.privacy.get_default', [$privacy, $context, $owner], true);

        return $privacyDetail ?? [];
    }

    protected function collectIcons(): array
    {
        return Cache::rememberForever('activity_feed_icons', function () {
            $items = app('events')->dispatch('activity.feed.collection_icons');

            $icons = [];

            foreach ($items as $item) {
                if (is_array($item)) {
                    $icons = array_merge($icons, $item);
                }
            }

            return $icons;
        });
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function archiveFeed(User $context, int $id): bool
    {
        $feed = $this
            ->with(['owner', 'item'])
            ->find($id);

        policy_authorize(FeedPolicy::class, 'removeFeed', $feed, $context, $feed->owner);

        $feed->pinned()->delete();
        $feed->update(['status' => MetaFoxConstant::ITEM_STATUS_REMOVED]);

        app('events')->dispatch('models.notify.removed', [$feed], true);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteFeedByUserAndOwner(User $context, Content $owner): void
    {
        $feeds = $this->getModel()->newQuery()
            ->where('owner_id', $owner->entityId())
            ->where('user_id', $context->entityId())
            ->get();
        foreach ($feeds as $feed) {
            $feed->delete();
        }
    }

    /**
     * @param User        $context
     * @param int         $lastFeedId
     * @param int         $lastPinFeedId
     * @param User|null   $owner
     * @param string|null $sort
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function hasNewFeeds(User $context, int $lastFeedId = 0, int $lastPinFeedId = 0, ?User $owner = null, ?string $sort = null, int $lastSponsoredFeedId = 0): bool
    {
        policy_authorize(FeedPolicy::class, 'viewAny', $context, $owner);

        if ($this->hasNewPinnedFeeds($context, $lastPinFeedId, $owner)) {
            return true;
        }

        if (null === $sort) {
            $sort = SortScope::SORT_DEFAULT;
        }

        $need = 1;

        $streamManager = $this->getStreamManager();

        $streamManager->setUserId($context->entityId())
            ->setSortFields($sort)
            ->setIsViewOnProfile(false)
            ->setPreviewTag(false)
            ->setIsGreaterThanLastFeed()
            ->setLimit($need);

        if ($owner instanceof User) {
            $streamManager->setOwnerId($owner->entityId());
        }

        $collection = new Collection();

        $streamManager->fetchPinnedFeeds();

        if ($lastSponsoredFeedId) {
            $streamManager->setLoadedSponsoredFeedIds([$lastSponsoredFeedId]);
        }

        $streamManager->fetchStreamContinuous($collection, $need, $lastFeedId, 0);

        return $collection->count() > 0;
    }

    protected function hasNewPinnedFeeds(User $context, int $lastPinFeedId, ?User $owner = null): bool
    {
        policy_authorize(FeedPolicy::class, 'viewAny', $context, $owner);

        if (0 == $lastPinFeedId) {
            $streamManager = $this->getStreamManager()
                ->setIsViewOnProfile(false);

            $streamManager->fetchPinnedFeeds();

            $pinnedFeedIds = $streamManager->getPinnedFeedIds();

            return count($pinnedFeedIds) > 0;
        }

        $isHomepage = null === $owner;

        $pinRepository = resolve(PinRepositoryInterface::class);

        $pinnedFeedQuery = $pinRepository->getModel()->newModelQuery();

        match ($isHomepage) {
            true  => $pinnedFeedQuery->whereNull('owner_id'),
            false => $pinnedFeedQuery->where('owner_id', '=', $owner->entityId())
        };

        $pinnedFeed = $pinnedFeedQuery
            ->where('feed_id', '=', $lastPinFeedId)
            ->first();

        if (null === $pinnedFeed) {
            return false;
        }

        $query = $pinRepository->getModel()->newModelQuery()
            ->where('id', '>', $pinnedFeed->entityId());

        match ($isHomepage) {
            true  => $query->whereNull('owner_id'),
            false => $query->where('owner_id', '=', $owner->entityId())
        };

        return $query->count() > 0;
    }

    public function countFeed(
        string  $ownerType,
        int     $ownerId,
        ?string $status = MetaFoxConstant::ITEM_STATUS_APPROVED,
        ?int    $userId = null
    ): int
    {
        $query = $this->getModel()->newModelQuery()
            ->where([
                'owner_type' => $ownerType,
                'owner_id'   => $ownerId,
            ]);

        if (null !== $status) {
            $query->where('status', '=', $status);
        }

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query->count();
    }

    public function handleRemoveNotification(string $notificationType, int $itemId, string $itemType): void
    {
        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            [$notificationType, $itemId, $itemType],
            true
        );
    }

    public function getMissingContentFeed(string $typeId): Collection
    {
        return $this->getModel()
            ->newQuery()
            ->where('type_id', $typeId)
            ->whereNull('activity_feeds.content')
            ->get();
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function approvePendingFeeds(User $user, User $owner): void
    {
        $items = $this->getModel()->newQuery()->where([
            'owner_id' => $owner->entityId(),
            'status'   => MetaFoxConstant::ITEM_STATUS_PENDING,
            'user_id'  => $user->entityId(),
        ])->get();

        foreach ($items as $item) {
            $this->approvePendingFeed($owner->user, $item->entityId());
        }
    }

    public function getPrivacyIds(User $owner, Feed $feed): array
    {
        return Stream::query()
            ->where([
                'feed_id'  => $feed->entityId(),
                'owner_id' => $owner->entityId(),
            ])
            ->select(['privacy_id'])
            ->pluck('privacy_id')
            ->toArray();
    }

    public function getSponsorPriceForPayment(User $user, ?string $currencyId = null): ?float
    {
        $userRole = resolve(RoleRepositoryInterface::class)->roleOf($user);

        $prices = $this->getSponsorPriceValue($userRole->entityId());

        if (!count($prices)) {
            return null;
        }

        if (null === $currencyId) {
            $currencyId = app('currency')->getUserCurrencyId($user);
        }

        $price = Arr::get($prices, $currencyId);

        if (!is_numeric($price)) {
            return null;
        }

        if ($price < 0) {
            return null;
        }

        return $price;
    }

    protected function getSponsorPriceValue(?int $roleId = null): array
    {
        $prices = Settings::get('activity.feed.purchase_sponsor_price');

        if (null === $prices) {
            return [];
        }

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (!is_array($prices)) {
            return [];
        }

        if (null === $roleId) {
            return $prices;
        }

        $rolePrices = Arr::get($prices, $roleId);

        if (!is_array($rolePrices)) {
            return [];
        }

        return $rolePrices;
    }

    public function createSchedulePost(User $context, User $user, User $owner, array $params): ActivitySchedule
    {
        $this->checkCreateFeedPolicy($context, $user, $owner, $params);
        if (!policy_check(FeedPolicy::class, 'schedulePost', $owner)) {
            abort(400, __('validation.no_permission'));
        }

        return resolve(ActivityScheduleRepositoryInterface::class)->createSchedule($context, $user, $owner, $params);
    }

    public function translateFeed(Feed $feed, User $context, array $params): array|null
    {
        policy_authorize(FeedPolicy::class, 'translate', $context);

        $text = $feed->content;

        if (null === $text) {
            return null;
        }

        $data = app('events')->dispatch('translation.translate', [$text, $feed, $context, $params], true);

        return $data;
    }

    private function checkCreateFeedPolicy(User $context, User $user, User $owner, array $params): void
    {
        policy_authorize(FeedPolicy::class, 'create', $context, $owner);

        app('events')->dispatch('feed.pre_composer_create', [$context, $params], true);

        $postType = Arr::get($params, 'post_type');

        unset($params['post_type']);

        if (!policy_check(FeedPolicy::class, 'hasCreateFeed', $owner, $postType)) {
            abort(400, __('validation.no_permission'));
        }

        $content = Arr::get($params, 'content', '');

        if (is_string($content) && '' !== $content && resolve(FeedRepositoryInterface::class)->checkSpamStatus($user, $postType, $content)) {
            abort(400, __p('core::phrase.you_have_already_added_this_recently_try_adding_something_else'));
        }
    }

    private function deleteRelatedItems(Feed $feed): void
    {
        if (!$feed->item instanceof ActivityFeedSource) {
            return;
        }

        app('events')->dispatch('activity.feed.deleted', [$feed->item]);
    }

    public function updateUserValueSortFeed(User $user, array $params): void
    {
        if (!$user instanceof \MetaFox\User\Models\User) {
            return;
        }

        $value       = Arr::get($params, 'sort', Browse::SORT_RECENT);
        $userId      = Arr::get($params, 'user_id', 0);
        $from        = UserSupport::KEY_SORT_FEED_VALUES_ON_HOME;
        $settingName = UserSupport::SORT_FEED_VALUES_SETTING;
        $values      = UserFacades::getReferenceValueByName($user, $settingName);

        if ($userId > 0) {
            $profile = UserEntity::getById($userId)->detail;
            $from    = sprintf('%s.%s', $profile->moduleName(), $profile->entityType());
        }

        if (Arr::has($values, $from) && Arr::get($values, $from) == $value) {
            return;
        }

        Arr::set($values, $from, $value);

        /**
         * @var UserPreferenceRepositoryInterface $userPreferencesRepository
         */
        $userPreferencesRepository = resolve(UserPreferenceRepositoryInterface::class);
        $userPreferencesRepository->updateOrCreatePreferences($user, [
            $settingName => $values,
        ]);

        $cacheKey = sprintf(CacheManager::USER_PREFERENCE_VALUE_BY_NAME_CACHE, $settingName, $user->entityType(), $user->entityId());

        Cache::forget($cacheKey);
    }
}
