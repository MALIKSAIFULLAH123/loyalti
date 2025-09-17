<?php

namespace MetaFox\Activity\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasBlockMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Trait HasExtra.
 *
 * @property Content $resource
 */
trait FeedExtra
{
    use HasExtra;

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getFeedExtra(): array
    {
        return array_merge(
            $this->getExtra(),
            $this->getBasePermissions(),
            $this->getExtraPermissions(),
            $this->getOwnerSpecificPermissions(),
            $this->getSharedItemPermissions()
        );
    }

    protected function getBasePermissions(): array
    {
        $feed    = $this->resource;
        $context = user();

        /** @var \MetaFox\Activity\Policies\FeedPolicy $feedPolicy */
        $feedPolicy = $this->getFeedPolicy();

        return [
            'can_pin_home'                 => $feedPolicy->pinHome($context, $feed),
            'can_pin_item'                 => $feedPolicy->pinItem($context, $feed),
            'can_hide_item'                => $feedPolicy->hideFeed($context, $feed),
            'can_hide_all_user'            => $feedPolicy->snoozeForever($context, $feed->user),
            'can_snooze_user'              => $feedPolicy->snooze($context, $feed->user),
            'can_remove_tag_friend'        => $feedPolicy->removeTag($feed),
            'can_change_privacy_from_feed' => $feedPolicy->changePrivacyFromFeed($context, $feed),
            'can_view_histories'           => $feedPolicy->viewHistory($context, $feed),
            'can_remove'                   => false,
            'can_hide_all_owner'           => false,
            'can_snooze_owner'             => false,
            'can_review_feed'              => $feedPolicy->reviewTagStreams($context, $feed),
            'can_edit_feed_item'           => $feedPolicy->updateFeedItem($context, $feed),
            'can_delete_with_items'        => $feedPolicy->deleteWithItems($context, $feed),
        ];
    }

    protected function getExtraPermissions(): array
    {
        $extraPermissions = app('events')
            ->dispatch('feed.permissions.extra', [user(), $this->resource]);

        if (!is_array($extraPermissions)) {
            return [];
        }

        $permissions = [];

        foreach ($extraPermissions as $extraPermission) {
            if (!is_array($extraPermission)) {
                continue;
            }

            if (!count($extraPermission)) {
                continue;
            }

            $permissions = array_merge($permissions, $extraPermission);
        }

        return $permissions;
    }

    protected function getOwnerSpecificPermissions(): array
    {
        $user  = $this->resource->user;
        $owner = $this->resource->owner;

        if (!$user instanceof User || !$owner instanceof User) {
            return [];
        }

        $context    = user();
        $feedPolicy = $this->getFeedPolicy();

        $permissions = [];

        if ($owner instanceof HasBlockMember) {
            $permissions['can_block'] = $feedPolicy->blockUser($context, $this->resource);
        }

        if ($user->entityId() == $owner->entityId()) {
            return $permissions;
        }

        return array_merge($permissions, [
            'can_hide_all_owner' => $feedPolicy->snoozeForever($context, $owner),
            'can_snooze_owner'   => $feedPolicy->snoozeOwner($context, $owner),
            'can_remove'         => $feedPolicy->removeFeed($this->resource, $context, $owner),
        ]);
    }

    protected function getSharedItemPermissions(): array
    {
        $item = $this->resource->item;

        if (!$item instanceof Share) {
            return [];
        }

        $item->loadMissing(['item']);
        $content = $item->item;

        if (!$content instanceof Content) {
            return [];
        }

        $user  = $this->resource->user;
        $owner = $this->resource->owner;

        $sharedUser  = $content->user;
        $sharedOwner = $content->owner;

        $context = user();

        /** @var \MetaFox\Activity\Policies\FeedPolicy $feedPolicy */
        $feedPolicy = $this->getFeedPolicy();

        $permissions = [];

        // If shared user is different from feed's user and feed's owner.
        if (
            $sharedUser->entityId() != $user->entityId()
            && $sharedUser->entityId() != $owner->entityId()
        ) {
            $permissions = array_merge($permissions, [
                'can_hide_all_shared_user' => $feedPolicy->snoozeForever($context, $sharedUser),
                'can_snooze_shared_user'   => $feedPolicy->snooze($context, $sharedUser),
            ]);
        }

        if (
            // If shared owner is different from feed's user and feed's owner.
            $sharedOwner->entityId() != $user->entityId()
            && $sharedOwner->entityId() != $owner->entityId()
            // And shared owner is different from shared user.
            && $sharedOwner->entityId() != $sharedUser->entityId()
        ) {
            $permissions = array_merge($permissions, [
                'can_hide_all_shared_owner' => $feedPolicy->snoozeForever($context, $sharedOwner),
                'can_snooze_shared_owner'   => $feedPolicy->snooze($context, $sharedOwner),
            ]);
        }

        return $permissions;
    }

    protected function getFeedPolicy()
    {
        return resolve('FeedPolicySingleton');
    }

    protected function isProfileFeed(): bool
    {
        return (bool) request()->get('is_profile_feed', null);
    }
}
