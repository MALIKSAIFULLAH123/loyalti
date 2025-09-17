<?php

namespace MetaFox\Activity\Support;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use MetaFox\Activity\Contracts\ActivityFeedContract;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Models\Stream;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Core\Models\Link;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTotalFeed;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Traits\Eloquent\Model\HasBackGroundStatusTrait;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\UserEntity as UserEntityFacades;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User as UserSupport;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class ActivityFeed.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActivityFeed implements ActivityFeedContract
{
    use CheckModeratorSettingTrait;
    use HasBackGroundStatusTrait {
        getBackgroundStatus as getBackgroundStatusTrait;
    }

    /** @var FeedRepositoryInterface */
    private FeedRepositoryInterface $feedRepository;

    /** @var TypeManager */
    private TypeManager $typeManager;

    public function __construct(
        FeedRepositoryInterface $feedRepository,
        TypeManager $typeManager
    ) {
        $this->feedRepository   = $feedRepository;
        $this->typeManager      = $typeManager;
    }

    /**
     * @param FeedAction $feedAction
     *
     * @return bool|Feed
     * @throws ValidatorException
     */
    public function createActivityFeed(FeedAction $feedAction): ?Feed
    {
        $owner = UserEntityFacades::getById($feedAction->getOwnerId())->detail;

        if (!policy_check(FeedPolicy::class, 'hasCreateFeed', $owner, $feedAction->getTypeId())) {
            return null;
        }

        if (!$feedAction->getItemId()) {
            return null;
        }

        return $this->feedRepository->create([
            'item_id'            => $feedAction->getItemId(),
            'item_type'          => $feedAction->getItemType(),
            'type_id'            => $feedAction->getTypeId(),
            'privacy'            => $feedAction->getPrivacy(),
            'user_id'            => $feedAction->getUserId(),
            'user_type'          => $feedAction->getUserType(),
            'owner_id'           => $feedAction->getOwnerId(),
            'owner_type'         => $feedAction->getOwnerType(),
            'content'            => $feedAction->getContent(),
            'status'             => $feedAction->getStatus(),
            'from_resource'      => $this->getFromResource($feedAction),
            'latest_activity_at' => Carbon::now(),
        ]);
    }

    protected function getFromResource(FeedAction $feedAction): string
    {
        $fromResource = Feed::FROM_APP_RESOURCE;

        if (method_exists($feedAction, 'getExtra')) {
            $fromResource = Arr::get($feedAction->getExtra(), 'from_resource') ?: $fromResource;
        }

        if (method_exists($feedAction, 'getFromResource')) {
            $fromResource = $feedAction->getFromResource() ?: $fromResource;
        }

        return $fromResource;
    }

    /**
     * Check exists before using this method.
     *
     * @param int $feedId
     *
     * @return bool
     * @todo if is activity post, delete activity post resource too ?
     */
    public function deleteActivityFeed(int $feedId): bool
    {
        return (bool) $this->feedRepository->delete($feedId);
    }

    /**
     * Create an activity post.
     *
     * @param string    $content
     * @param int       $privacy
     * @param User      $user
     * @param null|User $owner
     * @param int[]     $list
     * @param mixed     $relations
     *
     * @return Post
     */
    public function createActivityPost(
        string $content,
        int $privacy,
        User $user,
        ?User $owner = null,
        array $list = [],
        $relations = []
    ): Post {
        if ($owner === null) {
            $owner = $user;
        }

        $activityPost = new Post();

        $activityPost->fill([
            'content'    => $content,
            'privacy'    => $privacy,
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ]);
        if ($activityPost->privacy === MetaFoxPrivacy::CUSTOM) {
            $activityPost->privacy_list = $list;
        }
        $activityPost->save();
        $activityPost->loadMissing($relations);

        return $activityPost;
    }

    /**
     * Put Feed to stream.
     *
     * @param Feed  $feed
     * @param array $attributes
     */
    public function putToStream(Feed $feed, array $attributes = []): void
    {
        if (null === $feed->item) {
            return;
        }

        // Refresh model for latest data.
        $feed->refresh();

        $privacyUidList = match ($feed->item instanceof HasPrivacy) {
            true  => app('events')->dispatch('core.get_privacy_id', [$feed->itemId(), $feed->itemType()], true),
            false => $this->getPrivacyListForFeed($feed),
        };

        $mappingStatus = Arr::get($attributes, 'mapping_status_by_owner', []);
        $status        = Arr::get($mappingStatus, $feed->ownerId(), UserSupport::AUTO_APPROVED_TAGGER_POST);

        if (count($privacyUidList)) {
            foreach ($privacyUidList as $privacyUid) {
                $data           = [
                    'feed_id'    => $feed->entityId(),
                    'user_id'    => $feed->userId(),
                    'owner_id'   => $feed->ownerId(),
                    'owner_type' => $feed->ownerType(),
                    'item_id'    => $feed->item_id,
                    'item_type'  => $feed->item_type,
                    'privacy_id' => $privacyUid,
                    'created_at' => $feed->created_at,
                    'updated_at' => $feed->updated_at,
                ];
                $data['status'] = $status;

                $stream = new Stream($data);

                $stream->save(['timestamps' => false]);
            }
        }
    }

    protected function getPrivacyListForFeed(Feed $feed): array
    {
        return resolve(PrivacyRepositoryInterface::class)->getPrivacyIdsForContent($feed);
    }

    /**
     * @param Feed  $feed
     * @param User  $context
     * @param int   $userAutoTag
     * @param array $attributes
     *
     * @return void
     */
    public function putToTagStream(Feed $feed, User $context, int $userAutoTag, array $attributes = []): void
    {
        // Refresh model for latest data.
        $feed->refresh();

        if ($feed->owner instanceof HasPrivacyMember) {
            return;
        }

        $privacyUidList = app('events')->dispatch('core.get_privacy_id', [
            $feed->itemId(),
            $feed->itemType(),
        ], true);

        $streamQuery = Stream::query()->where([
            'feed_id'  => $feed->entityId(),
            'owner_id' => $context->ownerId(),
        ]);

        $mappingStatus = Arr::get($attributes, 'mapping_status_by_owner', []);

        if ($streamQuery->exists()) {
            $isAllowTaggerPost = UserSupport::AUTO_APPROVED_TAGGER_POST;

            if ($feed->userId() != $feed->ownerId() && $feed->owner instanceof HasUserProfile) {
                $isAllowTaggerPost = (int) UserValue::checkUserValueSettingByName($feed->owner, 'user_auto_add_tagger_post');
            }

            $streamQuery->update(['status' => Arr::get($mappingStatus, $context->ownerId(), $isAllowTaggerPost)]);

            return;
        }

        $status = Arr::get($mappingStatus, $context->ownerId(), $userAutoTag);

        if (!empty($privacyUidList)) {
            foreach ($privacyUidList as $privacyUid) {
                $stream = new Stream([
                    'feed_id'    => $feed->entityId(),
                    'user_id'    => $feed->userId(),
                    'owner_id'   => $context->ownerId(),
                    'owner_type' => $context->ownerType(),
                    'item_id'    => $feed->item_id,
                    'item_type'  => $feed->item_type,
                    'privacy_id' => $privacyUid,
                    'status'     => $status,
                    'created_at' => $feed->created_at,
                    'updated_at' => $feed->updated_at,
                ]);

                $stream->save(['timestamps' => false]);
            }
        }
    }

    /**
     * @param int $bgStatusId
     * @deprecated  Remove in 5.1.13
     *
     * @return array<string, mixed>|null
     */
    public function getBackgroundStatusImage(int $bgStatusId): ?array
    {
        if (0 == $bgStatusId) {
            return null;
        }

        if (!app_active('metafox/background-status')) {
            return null;
        }

        /** @var array<string, mixed>|null $image */
        $image = app('events')->dispatch('background-status.get_bg_status_image', [$bgStatusId], true);

        if (empty($image)) {
            return null;
        }

        return $image;
    }

    /**
     * @param int $bgStatusId
     *
     * @return JsonResource|array|null
     */
    public function getBackgroundStatus(int $bgStatusId): JsonResource|array|null
    {
        $backgroundStatus = $this->getBackgroundStatusTrait($bgStatusId);

        /*
         * @deprecated Remove in 5.1.13
         * @todo: remove return array
         */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.12', '<')) {
            return $backgroundStatus?->images;
        }

        return $backgroundStatus;
    }

    /**
     * @param int $shareId
     *
     * @return Feed|null
     */
    public function getFeedByShareId(int $shareId): ?Feed
    {
        return $this->feedRepository->getModel()->newModelQuery()
            ->where('item_id', $shareId)
            ->where('item_type', Share::ENTITY_TYPE)
            ->first();
    }

    /**
     * @param Feed $feed
     *
     * @return bool
     */
    public function sendFeedComposeNotification(Feed $feed): bool
    {
        $user = $feed->userEntity;

        $owner = $feed->ownerEntity;

        // Control checkpoint for $user and $owner
        if (!$user instanceof UserEntity || !$owner instanceof UserEntity) {
            return false;
        }

        /*
         * Send signal to other modules to trigger sending notification action.
         */
        try {
            app('events')->dispatch('feed.composer.notification', [$user, $owner, $feed], true);
        } catch (Exception $exception) {
            // Silent the error
            Log::error($exception->getMessage());
        }

        return true;
    }

    /**
     * @param string $ownerType
     * @param int    $ownerId
     *
     * @return void
     */
    public function deleteCoreFeedsByOwner(string $ownerType, int $ownerId): void
    {
        $query = $this->feedRepository->getModel()->newQuery();

        $itemTypes = [Post::ENTITY_TYPE, Link::ENTITY_TYPE];

        $feeds = $query
            ->whereIn('item_type', $itemTypes)
            ->where([
                'owner_id'   => $ownerId,
                'owner_type' => $ownerType,
            ])
            ->get();

        if (null !== $feeds) {
            foreach ($feeds as $feed) {
                $feed->delete();
            }
        }
    }

    /**
     * @param array $conditions
     *
     * @return void
     */
    public function deleteTagsStream(array $conditions): void
    {
        Stream::query()->where($conditions)->delete();
    }

    /**
     * @param User     $context
     * @param Content  $feed
     * @param int|null $representativePrivacy
     *
     * @return array
     */
    public function getPrivacyDetail(User $context, Content $feed, ?int $representativePrivacy = null): array
    {
        return $this->feedRepository->getPrivacyDetail($context, $feed, $representativePrivacy);
    }

    /**
     * @inheritDoc
     */
    public function createFeedFromFeedSource(Model $model, ?string $fromResource = Feed::FROM_APP_RESOURCE): ?Feed
    {
        if (!$model instanceof ActivityFeedSource) {
            return null;
        }

        LoadReduce::flush();

        /*
         * force load new to ensure not duplicate feeds for one item only
         */
        $model->load('activity_feed');

        if ($model->activity_feed?->exists()) {
            return null;
        }

        $feedAction = $model->toActivityFeed();

        if (!$feedAction instanceof FeedAction) {
            return null;
        }

        if ($fromResource == Feed::FROM_FEED_RESOURCE) {
            $feedAction->setFromResource($fromResource);
        }

        $feed = $this->createActivityFeed($feedAction);

        if (!$feed instanceof Feed) {
            return null;
        }

        // Further actions shall apply for content with its owner is a HasPrivacyMember
        if (!$model instanceof Content) {
            return null;
        }

        if (!$model->owner instanceof HasPrivacyMember) {
            return null;
        }

        $forcedStatus = Arr::get($feedAction->getExtra(), 'forced_status');

        if ($forcedStatus != MetaFoxConstant::ITEM_STATUS_APPROVED) {
            $this->handlePendingMode($model, $feed);
        }

        $model->refresh();

        if (!$model->isApproved()) {
            return null;
        }

        $owner = $model->owner;

        if ($owner instanceof HasTotalFeed) {
            $owner->incrementAmount('total_feed');
        }

        app('events')->dispatch(
            'activity.notify.approved_new_post_in_owner',
            [$feed, $feed->owner],
            true
        );

        return $feed;
    }

    /**
     * @param Content $model
     * @param Feed    $feed
     */
    protected function handlePendingMode(Content $model, Feed $feed): void
    {
        if (!$model instanceof Model) {
            return;
        }

        if (!$model instanceof HasApprove) {
            return;
        }

        if ($model->ownerId() == $model->userId()) {
            return;
        }

        $owner = $model->owner;

        $user = $model->user;

        if ($owner->hasPendingMode()) {
            $isApproved = true;

            if ($owner->isPendingMode()) {
                $isApproved = $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
            }

            $model->is_approved = $feed->is_approved = $isApproved;

            $model->save();

            $feed->save();

            if (!$isApproved) {
                app('events')->dispatch('models.notify.pending', [$feed], true);
            }
        }
    }
}
