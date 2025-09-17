<?php

namespace MetaFox\Activity\Policies;

use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Stream;
use MetaFox\Activity\Models\Type;
use MetaFox\Activity\Policies\Contracts\HideFeedPolicyInterface;
use MetaFox\Activity\Policies\Contracts\SnoozePolicyForFeedInterface;
use MetaFox\Activity\Policies\Traits\CheckPrivacyShareabilityTrait;
use MetaFox\Activity\Policies\Traits\HideFeedPolicyTrait;
use MetaFox\Activity\Policies\Traits\PinFeedPolicyTrait;
use MetaFox\Activity\Policies\Traits\SnoozePolicyForFeedTrait;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasBlockMember;
use MetaFox\Platform\Contracts\HasItemMorph;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasReportToOwner;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * @SuppressWarnings(PHPMD)
 */
class FeedPolicy implements
    ResourcePolicyInterface,
    HideFeedPolicyInterface,
    SnoozePolicyForFeedInterface
{
    use HasPolicyTrait;
    use HideFeedPolicyTrait;
    use PinFeedPolicyTrait;
    use CheckModeratorSettingTrait;
    use CheckPrivacyShareabilityTrait;
    use IsFriendTrait;
    use SnoozePolicyForFeedTrait;

    protected string $type = Feed::class;

    private function getTypeManager(): TypeManager
    {
        return resolve(TypeManager::class);
    }

    private function getActionItem(Feed $resource): ?Entity
    {
        return $this->getTypeManager()->hasFeature($resource->type_id, Type::ACTION_ON_FEED_TYPE)
            ? $resource
            : $resource->item;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('feed.view')) {
            return false;
        }

        if ($owner instanceof User) {
            // Check can view on owner.
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        // check user role permission
        if (!$user->hasPermissionTo('feed.view')) {
            return false;
        }

        $owner = $resource->owner;

        // When we view on specific resource, if owner deleted, we cannot see this resource.
        if (!$owner instanceof User) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        if (!$resource->isApproved()) {
            if ($user->can('update', [$owner, $owner])) {
                return true;
            }

            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        // Check can view on resource.
        if ($resource instanceof HasItemMorph) {
            // @todo check when embed object is null.
            if ($resource->item === null) {
                return false;
            }
            $item = $resource->item;

            if ($item->owner instanceof HasPrivacyMember) {
                return $this->viewContent($user, $item->owner, MetaFoxConstant::ITEM_STATUS_APPROVED, $user->entityId() == $resource->userId());
            }

            if ($user->entityId() == $owner->entityId()) {
                return true;
            }

            if (!PrivacyPolicy::checkPermission($user, $item)) {
                return false;
            }

            /*
             * If item is instanceof HasPrivacy, then we do not need to check privacy with Feed
             */
            if ($item instanceof HasPrivacy) {
                return true;
            }

            /*
             * Below code is using for checking privacy with item which is not instanceof HasPrivacy
             */
            if (!PrivacyPolicy::checkBlockUser($user, $owner)) {
                return false;
            }

            /**
             * Below code is using for checking privacy with item which is not instanceof HasPrivacy.
             */
            $privacyIds = resolve(FeedRepositoryInterface::class)->getPrivacyIds($owner, $resource);

            if (!PrivacyPolicy::checkItemPrivacy($user, $owner, $resource, $privacyIds)) {
                return false;
            }
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }
        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('feed.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                if (!$this->viewOwner($user, $owner)) {
                    return false;
                }
            }

            if (!PrivacyPolicy::checkCreateOnOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        if (!$this->getTypeManager()->hasFeature($resource->type_id, Type::CAN_EDIT_TYPE)) {
            return false;
        }

        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('feed.update')) {
            return false;
        }

        if ($this->isParentOwner($user, $resource->owner)) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function approve(User $user, ?Content $resource = null): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if ($resource instanceof Content) {
            $owner = $resource->owner;
            if ($owner instanceof HasPrivacyMember) {
                return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
            }
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if ($this->isParentOwner($user, $resource->owner)) {
            if (!$user->hasPermissionTo('feed.delete')) {
                return false;
            }

            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteWithItems(UserModel $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        if (resolve(TypeManager::class)->hasFeature($resource->type_id, Type::PREVENT_DELETE_FEED_ITEMS_TYPE)) {
            return false;
        }

        if (!$user->hasAdminRole()) {
            return false;
        }

        $item = $resource->item;

        if ($resource->from_resource == Feed::FROM_FEED_RESOURCE) {
            return false;
        }

        if (!$item instanceof ActivityFeedSource) {
            return false;
        }

        if ($item instanceof UserModel) {
            return false;
        }

        if (!$this->deleteItems($user, $item)) {
            return false;
        }

        return true;
    }

    private function deleteItems(UserModel $user, Entity $item): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($item));

        if (!is_object($policy)) {
            return false;
        }

        if (!method_exists($policy, 'delete')) {
            return false;
        }

        return $policy->delete($user, $item);
    }

    protected function isParentOwner(User $user, ?User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return false;
        }

        if (!method_exists($owner, 'isOwner')) {
            return false;
        }

        if (true === call_user_func([$owner, 'isOwner'], $user)) {
            return true;
        }

        return false;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('feed.delete')) {
            return false;
        }

        if ($resource instanceof Content) {
            $owner = $resource->owner;

            if (null === $owner) {
                return false;
            }

            if (method_exists($owner, 'hasDeleteFeedPermission')) {
                return call_user_func([$owner, 'hasDeleteFeedPermission'], $user, $resource);
            }

            if ($user->entityId() != $resource->userId()) {
                if ($owner instanceof HasPrivacyMember) {
                    return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
                }

                if ($owner->entityId() == $user->entityId()) {
                    return true;
                }

                if ($owner->userId() == $user->entityId()) {
                    return true;
                }

                return false;
            }
        }

        return true;
    }

    public function like(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $item = $this->getActionItem($resource);

        if (!$item instanceof HasTotalLike) {
            return false;
        }

        if (!app('events')->dispatch('like.can_like', [$item->entityType(), $user, $item], true)) {
            return false;
        }

        $resourceOwner = $resource->owner;

        if (!$resourceOwner instanceof User) {
            return false;
        }

        if (!$resourceOwner->isApproved()) {
            return false;
        }

        // Check permission on Like app before checking with entity
        if (!$user->hasPermissionTo('like.create')) {
            return false;
        }

        if (!$this->getTypeManager()->hasFeature($resource->type_id, Type::CAN_LIKE_TYPE)) {
            return false;
        }

        if (!$user->hasPermissionTo('feed.like')) {
            return false;
        }

        if (app('events')->dispatch('like.owner.can_like_item', [$user, $resourceOwner], true)) {
            return true;
        }

        return $this->checkCreateOnOwner($user, $resourceOwner);
    }

    public function share(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        if (!$this->isPrivacyShareable($resource->privacy)) {
            return false;
        }

        $item = $this->getActionItem($resource);

        if (!$item instanceof HasTotalShare) {
            return false;
        }

        if (!app('events')->dispatch('activity.feed.can_share', [$item->entityType(), $user, $item], true)) {
            return false;
        }

        $resourceOwner = $resource->owner;

        if (!$resourceOwner instanceof User) {
            return false;
        }

        if (!$resourceOwner->isApproved()) {
            return false;
        }

        if (!$this->getTypeManager()->hasFeature($resource->type_id, Type::CAN_SHARE_TYPE)) {
            return false;
        }

        if (!$user->hasPermissionTo('feed.share')) {
            return false;
        }

        if ($resourceOwner instanceof PostBy) {
            return $resourceOwner->checkContentShareable($user, $resourceOwner);
        }

        return true;
    }

    public function comment(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $item = $this->getActionItem($resource);

        if (!$item instanceof HasTotalComment) {
            return false;
        }

        if (!app('events')->dispatch('comment.can_comment', [$item->entityType(), $user, $item], true)) {
            return false;
        }

        $resourceOwner = $resource->owner;

        if (!$resourceOwner instanceof User) {
            return false;
        }

        if (!$resourceOwner->isApproved()) {
            return false;
        }

        // User of this role can comment
        if (!$user->hasPermissionTo('comment.comment')) {
            return false;
        }

        if (!$this->getTypeManager()->hasFeature($resource->type_id, Type::CAN_COMMENT_TYPE)) {
            return false;
        }

        if (!$user->hasPermissionTo('feed.comment')) {
            return false;
        }

        if (app('events')->dispatch('comment.owner.can_comment_item', [$user, $resourceOwner], true)) {
            return true;
        }

        $policy = PolicyGate::getPolicyFor(get_class($item));

        if (
            is_object($policy)
            && method_exists($policy, 'viewOwner')
            && !call_user_func([$policy, 'viewOwner'], $user, $resourceOwner)
        ) {
            return false;
        }

        return true;
    }

    private function checkCreateOnOwner(User $user, User $owner): bool
    {
        if ($owner->entityId() == $user->entityId()) {
            return true;
        }

        return PrivacyPolicy::checkCreateOnOwner($user, $owner);
    }

    public function reportItem(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $resourceOwner = $resource->owner;

        $feedUser = $resource->user;
        $item     = $resource->item;

        if ($feedUser instanceof User) {
            if ($feedUser->entityId() == $user->entityId()) {
                return false;
            }
        }

        if ($resourceOwner instanceof HasReportToOwner) {
            return $resourceOwner->canReportItem($user, $resource);
        }

        return $user->hasPermissionTo("{$item->entityType()}.report");
    }

    public function reportToOwner(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $feedUser = $resource->user;

        if ($feedUser instanceof User) {
            if ($feedUser->entityId() == $user->entityId()) {
                return false;
            }
        }

        $resourceOwner = $resource->owner;

        if ($resourceOwner instanceof HasReportToOwner) {
            return $resourceOwner->canReportToOwner($user, $resource);
        }

        return false;
    }

    public function saveItem(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $resourceOwner = $resource->owner;

        if (!$resourceOwner instanceof User) {
            return false;
        }

        if (!$this->checkCreateOnOwner($user, $resourceOwner)) {
            return false;
        }

        $item = $this->getActionItem($resource);

        if (!$item instanceof HasSavedItem) {
            return false;
        }

        $savedData = $item->toSavedItem();

        if (!count($savedData)) {
            return false;
        }

        $item = $resource->item;

        if (null === $item) {
            return false;
        }

        if (!$this->checkFromResource($user, $resource)) {
            return false;
        }

        return true;
    }

    private function checkFromResource(User $user, Feed $resource): bool
    {
        if ($resource->from_resource != Feed::FROM_FEED_RESOURCE) {
            return PolicyGate::check($resource->item->entityType(), 'saveItem', [$user, $resource->item]);
        }

        if (!$user->hasPermissionTo('saved.create')) {
            return false;
        }

        return $user->hasPermissionTo('feed.save');
    }

    public function isSavedItem(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $item = $this->getActionItem($resource);

        if (!$item instanceof HasSavedItem) {
            return false;
        }

        $savedData = $item->toSavedItem();

        if (!count($savedData)) {
            return false;
        }

        $item = $resource->item;

        if (null === $item) {
            return false;
        }

        return PolicyGate::check($item->entityType(), 'isSavedItem', [$user, $item]);
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'feed.view_wall')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }

    public function sponsor(User $user, ?Content $resource = null): bool
    {
        return false;
    }

    public function purchaseSponsor(User $user, ?Content $resource = null): bool
    {
        return false;
    }

    public function removeTag(?Feed $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        $context = user();
        $tags    = $this->getTaggedFriend($resource->item, $context);

        if (empty($tags)) {
            return false;
        }

        return true;
    }

    public function changePrivacyFromFeed(User $user, Feed $feed): bool
    {
        /*
         * Can not allow changing item privacy if it was posted in Page/Group/Event/Other User
         */
        if ($feed->userId() !== $feed->ownerId() || $feed->owner instanceof HasPrivacyMember) {
            return false;
        }

        /*
         * An activity type need to declare Type::CAN_CHANGE_PRIVACY_FROM_FEED_TYPE => true in its
         *
         * PackageSettingListener::getActivityTypes method for this permission
         */
        if (!$this->getTypeManager()->hasFeature($feed->type_id, Type::CAN_CHANGE_PRIVACY_FROM_FEED_TYPE)) {
            return false;
        }

        $item = $feed->item;

        /*
         * In case item has been deleted, then only check update privacy via feed permission
         */
        if (null === $item) {
            return $this->validateChangingPrivacyPermission($user, $feed);
        }

        if (true === $item?->unsupport_change_feed_privacy) {
            return false;
        }

        $policy = PolicyGate::getPolicyFor(get_class($item));

        if (is_object($policy) && method_exists($policy, 'update')) {
            return call_user_func([$policy, 'update'], $user, $item);
        }

        return $this->validateChangingPrivacyPermission($user, $feed);
    }

    protected function validateChangingPrivacyPermission(User $user, Feed $feed): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if ($user->entityId() != $feed->userId()) {
            return false;
        }

        if (!$user->hasPermissionTo('feed.update')) {
            return false;
        }

        return true;
    }

    public function viewContent(User $user, User $owner, string $status, bool $isYour = false): bool
    {
        $className = get_class($owner);

        $policy = PolicyGate::getPolicyFor($className);
        if (empty($policy)) {
            return false;
        }

        if (method_exists($policy, 'viewMyFeedContent') && $isYour) {
            return $policy->viewMyFeedContent($user, $owner, $status);
        }

        if (method_exists($policy, 'viewFeedContent')) {
            return $policy->viewFeedContent($user, $owner, $status, $isYour);
        }

        return true;
    }

    public function archive(User $user, Feed $resource): bool
    {
        if ($resource->is_removed) {
            return false;
        }

        return $this->delete($user, $resource);
    }

    public function removeFeed(Feed $resource, User $user, User $owner): bool
    {
        if (!$owner instanceof PostBy) {
            return false;
        }

        if ($resource->is_removed) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->userId() == $user->entityId()) {
            return false;
        }

        return $owner->hasRemoveFeed($user, $resource);
    }

    public function viewHistory(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        if (!$this->view($user, $resource)) {
            return false;
        }

        /* @link \MetaFox\Activity\Support\LoadMissingHasHistories::handle */
        return LoadReduce::get(
            sprintf('feed::hasHistories(feed:%s)', $resource->id),
            fn () => $resource->history()->exists()
        );
    }

    public function reviewTagStreams(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        /* @see \MetaFox\Activity\Support\LoadMissingReviewTagStreams::handle */
        return LoadReduce::get(
            sprintf('feed::hasReviewTagStreams(user:%s,feed:%s)', $user->id, $resource->id),
            fn () => $resource->stream()->where('owner_id', $user->entityId())
                ->where('status', Stream::STATUS_ALLOW)
                ->exists()
        );
    }

    public function blockUser(User $context, Content $resource): bool
    {
        $user  = $resource?->user;
        $owner = $resource?->owner;
        if ($user == null || $owner == null) {
            return true;
        }
        if ($owner instanceof HasBlockMember) {
            return $owner->canBlock($context, $user, $owner);
        }

        return true;
    }

    public function updateFeedItem(User $context, Content $resource): bool
    {
        if (!$resource instanceof Feed) {
            return false;
        }

        if ($this->getTypeManager()->hasFeature($resource->type_id, Type::PREVENT_EDIT_FEED_ITEM_TYPE)) {
            return false;
        }

        return $this->update($context, $resource);
    }

    public function pinItem(User $context, Content $resource): bool
    {
        if ($resource->isSponsored()) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($context->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if (!$context->hasPermissionTo('feed.pin')) {
            return false;
        }

        if (null === $resource->owner) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof HasPrivacyMember) {
            return $context->entityId() == $owner->entityId();
        }

        if ($context->hasPermissionTo(sprintf('%s.moderate', $owner->entityType()))) {
            return true;
        }

        if ($owner->isAdmin($context)) {
            return true;
        }

        return false;
    }

    public function pinHome(User $context, Content $resource): bool
    {
        if ($resource->isSponsored()) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($context->hasPermissionTo('feed.moderate')) {
            return true;
        }

        return $context->hasPermissionTo('feed.pin_home');
    }

    public function hasCreateFeed(User $owner, string $postType): bool
    {
        $allowSetting = !$owner instanceof HasPrivacyMember || !$owner->hasPendingMode();

        $setting = resolve(TypeManager::class)->hasFeature($postType, 'can_create_feed');

        if ($allowSetting && !$setting) {
            return false;
        }

        return true;
    }

    public function schedulePost(User $user): bool
    {
        if ($user->hasPermissionTo('feed.schedule_post')) {
            return true;
        }

        return false;
    }

    public function moderateScheduled(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        $owner = $resource->owner;
        if (null === $owner) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($this->isParentOwner($user, $resource->owner)) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember) {
            if ($resource->isUser($user)) {
                return true;
            }

            $policy = PolicyGate::getPolicyFor(get_class($owner));
            if (is_object($policy) && method_exists($policy, 'isPageAdmin')) {
                return call_user_func([$policy, 'isPageAdmin'], $user, $owner);
            }
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function translate(User $user): bool
    {
        if (!$user->hasPermissionTo('feed.translate')) {
            return false;
        }

        return true;
    }
}
