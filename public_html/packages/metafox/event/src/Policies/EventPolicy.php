<?php

namespace MetaFox\Event\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Models\Invite;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class EventPolicy.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EventPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use HandlesAuthorization;
    use CheckModeratorSettingTrait;

    protected string $type = Event::ENTITY_TYPE;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('event.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
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

        if (!UserPrivacy::hasAccess($user, $owner, 'event.view_browse_events')) {
            return false;
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        if (!$resource instanceof Event) {
            return false;
        }
        $owner = $resource->owner;

        /**
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */

        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('event.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (PrivacyPolicy::checkPermission($user, $resource) == false) {
            return false;
        }

        // Check setting view on resource.
        if ($isApproved) {
            return true;
        }

        if ($resource->owner instanceof HasPrivacyMember) {
            if ($this->checkModeratorSetting($user, $resource->owner, 'approve_or_deny_post')) {
                return true;
            }

            if (!$resource->isUser($user)) {
                return false;
            }
        }

        if ($user->hasPermissionTo('event.approve')) {
            return true;
        }

        return $user->entityId() == $resource->userId();
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('event.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                if ($owner->entityType() == 'user') {
                    return false;
                }

                // Check can view on owner.
                if (!PrivacyPolicy::checkCreateOnOwner($user, $owner)) {
                    return false;
                }

                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'event.share_events')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('event.update')) {
            return false;
        }

        if (!$resource instanceof Event) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if ($resource->isModerator($user)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('event.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('event.delete')) {
            return false;
        }

        if (!$resource instanceof Event) {
            return false;
        }

        if ($resource->isAdmin($user)) {
            return true;
        }

        $owner = $resource->owner;

        if (!$owner instanceof HasPrivacyMember) {
            return $resource->isAdmin($user);
        }

        if (!$resource->isApproved()) {
            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
    }

    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return bool
     */
    public function managePendingPosts(User $user, Content $resource): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        if (!$resource->isPendingMode()) {
            return false;
        }

        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if (UserPrivacy::hasAccess($user, $resource, 'event.manage_pending_post')) {
            return true;
        }

        return false;
    }

    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return bool
     */
    public function createDiscussion(User $user, Content $resource): bool
    {
        if (!$resource->isApproved()) {
            return false;
        }

        if (!$user->hasPermissionTo('event.discussion')) {
            return false;
        }

        if ($resource instanceof User) {
            if (UserPrivacy::hasAccess($user, $resource, 'feed.share_on_wall')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return bool
     */
    public function viewDiscussion(User $user, Content $resource): bool
    {
        if ($resource instanceof User) {
            if (UserPrivacy::hasAccess($user, $resource, 'feed.view_wall')) {
                return true;
            }
        }

        return false;
    }

    public function updateRsvp(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        // Rule: host/co-host can not modify rsvp status
        return !$resource->isModerator($user);
    }

    public function invite(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($user->isGuest()) {
            return false;
        }

        $ownerResource = $resource->owner;
        if ($ownerResource instanceof HasPrivacyMember) {
            $inviteMemberOnly = method_exists($ownerResource, 'isInviteMembers') && call_user_func([$ownerResource, 'isInviteMembers']);

            if ($inviteMemberOnly) {
                return $ownerResource->isMember($user);
            }

            return true;
        }

        if (!$this->checkInviteFriendPrivacy($user, $resource)) {
            return false;
        }

        return true;
    }

    protected function checkInviteFriendPrivacy(User $user, Event $event): bool
    {
        /*
         * Everyone/Community: All users can invite except guest & blocked users
         * Friend/Only me: Only owner
         * Friends of Friends: Owner and his/her friends
         * Custom: Owners and friends in these friend lists
         */

        if (in_array($event->privacy, [MetaFoxPrivacy::EVERYONE, MetaFoxPrivacy::MEMBERS])) {
            if ($event->privacy == MetaFoxPrivacy::EVERYONE && $user->isGuest()) {
                return false;
            }

            return PrivacyPolicy::checkPermission($user, $event);
        }

        if ($event->privacy == MetaFoxPrivacy::ONLY_ME) {
            return false;
        }

        if (null === $event->owner) {
            return false;
        }

        if ($event->isOwner($user)) {
            return true;
        }

        if ($event->privacy == MetaFoxPrivacy::FRIENDS) {
            return false;
        }

        if ($event->privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS) {
            return app('events')->dispatch('friend.is_friend', [$user->entityId(), $event->ownerId()], true) ?? false;
        }

        if ($event->privacy == MetaFoxPrivacy::CUSTOM) {
            return PrivacyPolicy::checkItemPrivacy($user, $event->owner, $event);
        }

        return false;
    }

    public function manageHosts(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('event.update')) {
            return false;
        }

        if ($resource->isEnded()) {
            return false;
        }

        return $resource->isAdmin($user);
    }

    /**
     * @param User $user
     * @param User $resource
     *
     * @return bool
     */
    public function viewHosts(User $user, User $resource): bool
    {
        if (!$this->view($user, $resource)) {
            return false;
        }

        return UserPrivacy::hasAccess($user, $resource, 'event.view_hosts');
    }

    /**
     * @param User $user
     * @param User $resource
     *
     * @return bool
     */
    public function viewMembers(User $user, User $resource): bool
    {
        if (!$this->view($user, $resource)) {
            return false;
        }

        return UserPrivacy::hasAccess($user, $resource, 'event.view_members');
    }

    /**
     * viewFeedContent.
     *
     * @param User   $user
     * @param Event  $event
     * @param string $status
     * @param bool   $isYour
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function viewFeedContent(User $user, Event $event, string $status, bool $isYour = false): bool
    {
        if ($event->isAdmin($user)) {
            return true;
        }

        switch ($status) {
            case MetaFoxConstant::ITEM_STATUS_APPROVED:
                $granted = UserPrivacy::hasAccess($user, $event, 'feed.view_wall');
                break;
            case MetaFoxConstant::ITEM_STATUS_PENDING:
                $granted = match ($isYour) {
                    true  => true,
                    false => $this->managePendingPosts($user, $event),
                };
                break;
            default:
                $granted = false;
                break;
        }

        return $granted;
    }

    public function massEmail(User $user, Event $event): bool
    {
        if (!$user->hasPermissionTo('event.mass_email')) {
            return false;
        }

        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if ($event->isEnded()) {
            return false;
        }

        return $event->isAdmin($user);
    }

    public function massInvite(User $user, Event $event): bool
    {
        $owner = $event->owner;
        if (!$owner instanceof HasPrivacyMember) {
            return false;
        }

        if (!app('events')->dispatch("{$owner->entityType()}.can_mass_invite_item", [$owner, $user], true)) {
            return false;
        }

        return $event->isAdmin($user);
    }

    public function removeInvite(User $user, Invite $invite): bool
    {
        if ($user->entityId() == $invite->userId()) {
            return true;
        }

        return $this->update($user, $invite->event);
    }

    public function removeInviteHost(User $user, HostInvite $invite): bool
    {
        if ($user->entityId() == $invite->userId()) {
            return true;
        }

        return $this->update($user, $invite->event);
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('event.auto_approved');
        }

        if (!$owner->hasPendingMode()) {
            return true;
        }

        if ($owner->isPendingMode()) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'event.view_browse_events')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }

    public function uploadBanner(User $user, ?Entity $resource = null): bool
    {
        return $this->update($user, $resource);
    }

    public function duplicateEvent(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        $owner = $resource->owner;

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember && !$owner->isAdmin($user)) {
            return false;
        }

        return $this->create($user, $owner);
    }

    public function export(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        return true;
    }
}
