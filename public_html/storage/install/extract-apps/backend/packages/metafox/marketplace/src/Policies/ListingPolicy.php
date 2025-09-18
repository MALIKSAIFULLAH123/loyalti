<?php

namespace MetaFox\Marketplace\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Support\Facade\Listing as Facade;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class ListingPolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ListingPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('marketplace.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('marketplace.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$resource instanceof Listing) {
            return false;
        }

        $isApproved = $resource->isApproved();
        $owner      = $resource->owner;

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        /*
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo('marketplace.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('marketplace.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        if ($resource->userId() == $user->entityId()) {
            return true;
        }

        if (!$isApproved) {
            if ($user->hasPermissionTo('marketplace.approve')) {
                return true;
            }

            if ($owner instanceof HasPrivacyMember) {
                return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
            }

            return false;
        }

        if ($resource->is_expired) {
            return $user->hasPermissionTo('marketplace.view_expired');
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

        if (!UserPrivacy::hasAccess($user, $owner, 'marketplace.view_browse_marketplace_listings')) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('marketplace.create')) {
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

                if (!UserPrivacy::hasAccess($user, $owner, 'marketplace.share_marketplace_listings')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('marketplace.moderate')) {
            return true;
        }

        if (!$this->updateOwn($user, $resource)) {
            return false;
        }

        return true;
    }

    public function updateOwn(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('marketplace.update')) {
            return false;
        }

        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('marketplace.moderate')) {
            return true;
        }

        if (!$resource instanceof Listing) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('marketplace.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('marketplace.delete')) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        $owner = $resource->owner;

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if (!$owner instanceof HasPrivacyMember) {
            return $user->entityId() == $resource->userId();
        }

        if (!$resource->isApproved()) {
            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
    }

    public function payment(User $user, ?Listing $listing): bool
    {
        if (null === $listing) {
            return false;
        }

        if ($user->isGuest()) {
            return false;
        }

        if (!$this->view($user, $listing)) {
            return false;
        }

        if ($user->entityId() == $listing->userId()) {
            return false;
        }

        if (!$listing->isApproved()) {
            return false;
        }

        if ($listing->is_sold) {
            return false;
        }

        if (Facade::isExpired($listing)) {
            return false;
        }

        if (!$listing->allow_payment && !$listing->allow_point_payment) {
            return false;
        }

        if (!$listing->user->hasPermissionTo('marketplace.sell_items')) {
            return false;
        }

        $prices = $listing->price;

        if (!count($prices)) {
            return false;
        }

        $price = Facade::getUserPrice($user, $prices);

        if (null === $price) {
            return false;
        }

        if ($price == 0) {
            return false;
        }

        return true;
    }

    protected function validateItemInvite(User $user, ?Listing $listing): bool
    {
        if (null === $listing) {
            return false;
        }

        if (!$listing->isApproved()) {
            return false;
        }

        if ($listing->is_sold) {
            return false;
        }

        if (Facade::isExpired($listing)) {
            return false;
        }

        if (null === $listing->owner) {
            return false;
        }

        if (!$this->viewOwner($user, $listing->owner)) {
            return false;
        }

        return true;
    }

    public function invite(User $user, ?Listing $listing): bool
    {
        /*
         * All endpoints for friends/members is on Friend app. Consider to move for invite members
         */
        if (!app_active('metafox/friend')) {
            return false;
        }

        if (!$this->validateItemInvite($user, $listing)) {
            return false;
        }

        if ($listing->owner instanceof HasPrivacyMember) {
            $inviteMemberOnly = method_exists($listing->owner, 'isInviteMembers') && call_user_func([$listing->owner, 'isInviteMembers']);

            if ($inviteMemberOnly) {
                return $listing->owner->isMember($user);
            }

            return true;
        }

        if (!$this->checkInviteFriendPrivacy($user, $listing)) {
            return false;
        }

        return true;
    }

    protected function checkInviteFriendPrivacy(User $user, Listing $listing): bool
    {
        /*
         * Everyone/Community: All users can invite except guest & blocked users
         * Friend/Only me: Only owner
         * Friends of Friends: Owner and his/her friends
         * Custom: Owners and friends in these friend lists
         */

        if (in_array($listing->privacy, [MetaFoxPrivacy::EVERYONE, MetaFoxPrivacy::MEMBERS])) {
            if ($listing->privacy == MetaFoxPrivacy::EVERYONE && $user->isGuest()) {
                return false;
            }

            return PrivacyPolicy::checkPermission($user, $listing);
        }

        if ($listing->privacy == MetaFoxPrivacy::ONLY_ME) {
            return false;
        }

        if (null === $listing->owner) {
            return false;
        }

        if ($listing->isOwner($user)) {
            return true;
        }

        if ($listing->privacy == MetaFoxPrivacy::FRIENDS) {
            return false;
        }

        if ($listing->privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS) {
            return app('events')->dispatch('friend.is_friend', [$user->entityId(), $listing->ownerId()], true) ?? false;
        }

        if ($listing->privacy == MetaFoxPrivacy::CUSTOM) {
            return PrivacyPolicy::checkItemPrivacy($user, $listing->owner, $listing);
        }

        return false;
    }

    public function message(User $user, ?Listing $listing): bool
    {
        if (null === $listing) {
            return false;
        }

        if ($user->isGuest()) {
            return false;
        }

        $owner = $listing->user;

        if (null == $owner) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return false;
        }

        if (UserBlocked::isBlocked($user, $owner)) {
            return false;
        }

        if (UserBlocked::isBlocked($owner, $user)) {
            return false;
        }

        if ($this->checkChatplusActive($user, $owner)) {
            return true;
        }

        if ($this->checkChatActive($user)) {
            return true;
        }

        $active = app('events')->dispatch('message.active', [$user, $owner], true);

        if (is_bool($active)) {
            return $active;
        }

        return false;
    }

    protected function checkChatplusActive(User $user, User $owner): bool
    {
        $response = app('events')->dispatch('chatplus.message.active', [$user, $owner], true);

        if (is_bool($response)) {
            return $response;
        }

        if (!app_active('metafox/chatplus')) {
            return false;
        }

        if (!Settings::get('chatplus.server')) {
            return false;
        }

        return true;
    }

    protected function checkChatActive(User $user): bool
    {
        $response = app('events')->dispatch('chat.message.active', [$user], true);

        if (is_bool($response)) {
            return $response;
        }

        if (!app_active('metafox/chat')) {
            return false;
        }

        if (!Settings::get('broadcast.connections.pusher.key')) {
            return false;
        }

        return true;
    }

    public function reopen(User $user, ?Listing $listing): bool
    {
        if (null === $listing) {
            return false;
        }

        if (!Facade::isExpired($listing)) {
            return false;
        }

        if ($user->hasPermissionTo('marketplace.reopen_expired')) {
            return true;
        }

        if (!$this->reopenOwn($user, $listing)) {
            return false;
        }

        return true;
    }

    public function reopenOwn(User $user, ?Listing $listing): bool
    {
        if (!$user->hasPermissionTo('marketplace.reopen_own_expired')) {
            return false;
        }

        if ($listing instanceof Content) {
            if ($user->entityId() != $listing->userId()) {
                return false;
            }
        }

        return true;
    }

    public function viewExpire(User $user, User $owner, int $profileId): bool
    {
        if (0 === $profileId) {
            return $this->hasModerateViewExpiredPermission($user);
        }

        if (!$owner instanceof HasPrivacyMember) {
            if ($owner->entityId() == $user->entityId()) {
                return true;
            }

            return $this->hasModerateViewExpiredPermission($user);
        }

        if ($owner->isAdmin($user)) {
            return true;
        }

        if ($user->hasPermissionTo($owner->entityType() . '.moderate')) {
            return true;
        }

        return false;
    }

    public function hasModerateViewExpiredPermission(User $user): bool
    {
        if ($user->hasPermissionTo('marketplace.moderate')) {
            return true;
        }

        if ($user->hasPermissionTo('marketplace.view_expired')) {
            return true;
        }

        return false;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'marketplace.view_browse_marketplace_listings')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('marketplace.auto_approved');
        }

        if (!$owner->hasPendingMode()) {
            return true;
        }

        if ($owner->isPendingMode()) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        return true;
    }

    public function sponsorItem(User $user, Content $resource): bool
    {
        if (!$resource instanceof Listing) {
            return false;
        }

        if ($resource->is_expired) {
            return false;
        }

        if ($resource->is_sold) {
            return false;
        }

        return true;
    }

    public function featureItem(User $user, Content $resource): bool
    {
        if (!$resource instanceof Listing) {
            return false;
        }

        if ($resource->is_expired) {
            return false;
        }

        if ($resource->is_sold) {
            return false;
        }

        return true;
    }
}
