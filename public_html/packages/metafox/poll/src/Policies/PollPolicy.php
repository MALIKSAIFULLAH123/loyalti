<?php

namespace MetaFox\Poll\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class PollPolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PollPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('poll.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('poll.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($this->viewOwner($user, $owner) == false) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        /*
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo('poll.moderate')) {
            return true;
        }

        // check user role permission
        if (!$user->hasPermissionTo('poll.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        if ($isApproved) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember) {
            if ($this->checkModeratorSetting($user, $owner, 'approve_or_deny_post')) {
                return true;
            }
        }

        if ($resource->userId() == $user->entityId()) {
            return true;
        }

        return $user->hasPermissionTo('poll.approve');
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

        if (!UserPrivacy::hasAccess($user, $owner, 'poll.view_browse_polls')) {
            return false;
        }

        return true;
    }

    public function create(User $user, User $owner = null): bool
    {
        if (!$user->hasPermissionTo('poll.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                if ($owner->entityType() == 'user') {
                    return false;
                }

                // Check can view on owner.
                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!PrivacyPolicy::checkCreateOnOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'poll.share_polls')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function copy(User $user): bool
    {
        if (!$user->hasPermissionTo('poll.create')) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Poll) {
            return false;
        }

        // If poll is closed, no one can edit it anymore
        if ($resource->is_closed) {
            return false;
        }

        if ($user->hasPermissionTo('poll.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Content $resource = null): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$user->hasPermissionTo('poll.update')) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User|Model  $user
     * @param Entity|null $resource
     *
     * @return bool
     */
    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('poll.moderate')) {
            return true;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('poll.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    /**
     * @param User        $user
     * @param Entity|null $resource
     *
     * @return bool
     */
    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('poll.delete')) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        $owner = $resource->owner;

        if (!$owner instanceof HasPrivacyMember) {
            return $user->entityId() == $resource->userId();
        }

        if (!$resource->isApproved()) {
            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
    }

    public function vote(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$this->voteOwn($user, $resource)) {
            return false;
        }

        if (!$this->voteAgain($user, $resource)) {
            return false;
        }

        return true;
    }

    public function voteOwn(User $user, ?Content $resource = null): bool
    {
        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource?->isUser($user)) {
            return $user->hasPermissionTo('poll.vote_own');
        }

        if (!$this->view($user, $resource)) {
            return false;
        }

        return true;
    }

    public function voteAgain(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Poll) {
            return true;
        }

        return !resolve(PollRepositoryInterface::class)
            ->isUserVoted($user, $resource->entityId());
    }

    public function changeVote(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('poll.change_own_vote')) {
            return false;
        }

        if (null === $resource) {
            return true;
        }

        if ($resource->is_closed) {
            return false;
        }

        $votedIds = $resource->results->pluck('user_id')->toArray();

        if (!in_array($user->entityId(), $votedIds)) {
            return false;
        }

        return true;
    }

    public function viewHiddenVote(User $user, ?Content $resource = null): bool
    {
        if ($resource instanceof Poll) {
            $owner = $resource->owner;

            if ($owner instanceof User) {
                if ($user->entityId() == $owner->entityId()) {
                    return true;
                }
            }

            if (!$resource->public_vote) {
                return true;
            }
        }

        return false;
    }

    public function viewResult(User $user, ?Content $resource): bool
    {
        if (!$resource instanceof Poll) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($resource->public_vote) {
            return true;
        }

        if ($resource->userId() == $user->entityId()) {
            return true;
        }

        return false;
    }

    public function viewResultBeforeVote(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Poll) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if ($user->hasPermissionTo('poll.view_result_before_vote')) {
            return true;
        }

        $votedIds = $resource->results->pluck('user_id')->toArray();
        if (in_array($user->entityId(), $votedIds)) {
            return true;
        }

        return false;
    }

    public function viewResultAfterVote(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Poll) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if ($user->hasPermissionTo('poll.view_result_after_vote')) {
            return true;
        }

        return false;
    }

    public function uploadImage(User $user): bool
    {
        return $user->hasPermissionTo('poll.upload_image');
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('poll.auto_approved');
        }

        if (!$owner->hasPendingMode()) {
            return true;
        }

        if ($owner->isPendingMode()) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        return true;
    }

    public function hasCreateFeed(User $owner, string $postType): bool
    {
        $allowSetting = !$owner instanceof HasPrivacyMember || !$owner->hasPendingMode();

        $setting = app('events')->dispatch('activity.has_feature', [$postType, 'can_create_feed'], true);

        if ($allowSetting && !$setting) {
            return false;
        }

        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'poll.view_browse_polls')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }
}
