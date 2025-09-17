<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Core\Models\Privacy;
use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class PrivacyPolicyRepository.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @see \MetaFox\Platform\Support\Facades\PrivacyPolicy
 */
class PrivacyPolicyRepository implements PrivacyPolicy
{
    public function checkPermission(?User $user, Entity $content): bool
    {
        if ($user == null) {
            return false;
        }

        if (!$content instanceof HasPrivacy) {
            return true;
        }

        if (!$content instanceof Content) {
            return true;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        // Owner.
        if ($content->ownerId() == $user->entityId()) {
            return true;
        }

        if ($content->owner instanceof HasPrivacyMember) {
            // User.
            if ($content->userId() == $user->entityId()) {
                return true;
            }
        }

        $owner = $content->user;
        if (!$owner instanceof User) {
            return false;
        }

        if (!$this->checkBlockUser($user, $owner)) {
            return false;
        }

        return $this->checkItemPrivacy($user, $owner, $content);
    }

    public function checkBlockUser(User $user, User $owner): bool
    {
        // If viewer blocked user who created the content.
        if (UserBlocked::isBlocked($user, $owner)) {
            return false;
        }

        // If viewer was blocked by user who created the content.
        if (UserBlocked::isBlocked($owner, $user)) {
            return false;
        }

        return true;
    }

    public function checkItemPrivacy(User $user, User $owner, HasPrivacy $content, ?array $privacyIds = null): bool
    {
        // If resource has no privacy.
        if (null === $content->privacy) {
            return true;
        }

        if (MetaFoxPrivacy::EVERYONE == $content->privacy) {
            return true;
        }

        if (MetaFoxPrivacy::ONLY_ME == $content->privacy) {
            return false;
        }

        if (MetaFoxPrivacy::MEMBERS == $content->privacy) {
            return !$user->isGuest();
        }

        if (null === $privacyIds) {
            $privacyIds = $this->getResourcePrivacyList($content);
        }

        if (!count($privacyIds)) {
            return false;
        }

        switch ($content->privacy) {
            case MetaFoxPrivacy::FRIENDS:
            case MetaFoxPrivacy::CUSTOM:
                return $this->checkHasAbility($user, $privacyIds, $content->privacy);
            case MetaFoxPrivacy::FRIENDS_OF_FRIENDS:
                // If friends, return true.
                $hasAbility = $this->checkHasAbility($user, $privacyIds, MetaFoxPrivacy::FRIENDS);

                if ($hasAbility) {
                    return true;
                }

                // If is friend of friend, return true. Everything else return false.
                $isFriendOfFriend = false;

                if (!$user->isGuest() && app_active('metafox/friend')) {
                    /** @var bool $isFriendOfFriend */
                    $isFriendOfFriend = app('events')->dispatch('friend.is_friend_of_friend', [$user->entityId(), $owner->entityId()], true);
                }

                return $isFriendOfFriend;
        }

        return false;
    }

    public function getResourcePrivacyList(Content $content): array
    {
        if ($content instanceof HasResourceStream) {
            /** @var Builder $streamBuilder */
            $streamBuilder = $content->privacyStreams();

            return $streamBuilder->getModel()->query()
                ->where('item_id', '=', $content->entityId())
                ->get(['privacy_id'])
                ->pluck('privacy_id')
                ->toArray();
        }

        return PrivacyStream::query()
            ->where('item_id', '=', $content->entityId())
            ->where('item_type', '=', $content->entityType())
            ->get(['privacy_id'])
            ->pluck('privacy_id')
            ->toArray();
    }

    public function getPrivacyItem(Content $content): array
    {
        $privacyIds = $this->getResourcePrivacyList($content);

        $items = [];

        if (!empty($privacyIds)) {
            $items = Privacy::query()
                ->whereIn('privacy_id', $privacyIds)
                ->get(['item_id', 'item_type'])
                ->toArray();
        }

        return $items;
    }

    /** @var array<string, bool> */
    private array $permissionOnOwner = [];

    private function checkPermissionOwnerCache(User $user, ?User $owner): ?bool
    {
        $key = $user->entityId() . '_' . $owner?->entityId();

        return array_key_exists($key, $this->permissionOnOwner) ? $this->permissionOnOwner[$key] : null;
    }

    private function setPermissionOwnerCache(User $user, User $owner, bool $value): void
    {
        $key = $user->entityId() . '_' . $owner->entityId();

        $this->permissionOnOwner[$key] = $value;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function checkPermissionOwner(User $user, ?User $owner, bool $cache = true): bool
    {
        if (!$owner) {
            return false;
        }
        $getFromCache = $cache ? $this->checkPermissionOwnerCache($user, $owner) : null;

        if ($getFromCache !== null) {
            return $getFromCache;
        }

        $value = $this->handleCheckPermissionOwner($user, $owner);

        $this->setPermissionOwnerCache($user, $owner, $value);

        return $value;
    }

    private function handleCheckPermissionOwner(User $user, User $owner): bool
    {
        // Owner.
        if ($owner->entityId() == $user->entityId()) {
            return true;
        }

        if ($owner->userId() == $user->entityId()) {
            return true;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if (!$this->checkBlockUser($user, $owner)) {
            return false;
        }

        // If owner has no privacy.
        if (!$owner instanceof HasPrivacy) {
            return true;
        }

        // In case admin site must view any resources
        if (method_exists($owner, 'hasResourceModeration')) {
            if ($owner->hasResourceModeration($user)) {
                return true;
            }
        }

        if ($owner->hasContentPrivacy()) {
            return $this->checkPermission($user, $owner);
        }

        if (in_array($owner->privacy, [MetaFoxPrivacy::EVERYONE, MetaFoxPrivacy::MEMBERS])) {
            return $this->checkPublicPermissionOwner($user, $owner);
        }

        if ($owner->privacy == MetaFoxPrivacy::ONLY_ME) {
            return false;
        }

        switch ($owner->privacy) {
            case MetaFoxPrivacy::FRIENDS:
            case MetaFoxPrivacy::CUSTOM:
                if ($this->hasAbilityOnOwner($user, $owner, $owner->privacy)) {
                    return true;
                }

                return false;
            case MetaFoxPrivacy::FRIENDS_OF_FRIENDS:
                // @todo NamNV TBD.
                return false;
        }

        return false;
    }

    protected function checkPublicPermissionOwner(User $user, User $owner): bool
    {
        $can = true;

        if ($owner->privacy == MetaFoxPrivacy::MEMBERS) {
            $can = !$user->isGuest();
        }

        if (!$can) {
            return false;
        }

        if (method_exists($owner, 'checkContentPermissionOnOwner')) {
            return call_user_func([$owner, 'checkContentPermissionOnOwner'], $user);
        }

        return true;
    }

    public function hasAbilityOnOwner(User $user, User $owner, int $privacy, string $privacyType = null): bool
    {
        $userId = $user->entityId();
        $ownerId = $owner->entityId();
        $data = LoadReduce::get(sprintf('privacy::hasAbilityOnOwner(user:%s,owner:%s)', $userId, $ownerId));

        if (isset($data)) {
            foreach ($data as $item) {
                if ($item['privacy'] !== $privacy) {
                    continue;
                }

                if (null !== $privacyType) {
                    // Specific find privacy type.
                    return $privacyType === $item['privacy_type'];
                }

                return true;
            }

            return false;
        }

        $query = DB::table('core_privacy_members as member')
            ->join('core_privacy as privacy', function (JoinClause $join) use ($user) {
                $join->on('member.privacy_id', '=', 'privacy.privacy_id');
                $join->where('member.user_id', '=', $user->entityId());
            })
            ->where('item_id', '=', $owner->entityId())
            ->where('item_type', '=', $owner->entityType())
            ->where('privacy', '=', $privacy);

        // Specific find privacy type.
        if (null !== $privacyType) {
            $query->where('privacy_type', $privacyType);
        }

        return $query->exists();
    }

    public function checkCreateOnOwner(User $user, User $owner): bool
    {
        if (UserBlocked::isBlocked($owner, $user)) {
            return false;
        }

        // Check if an user can post on owner.
        if ($owner instanceof PostBy) {
            // Example
            // Group: will return true only if current user is member.
            // Page: will always return true.
            // User: will always return true.
            if ($owner->checkPostBy($user)) {
                return true;
            }
        }

        return false;
    }

    public function checkCreateResourceOnOwner(Content $content): bool
    {
        $owner = $content->owner;

        $user = $content->user;

        if (!$this->checkBlockUser($user, $owner)) {
            return false;
        }

        // Now check if an user can post on owner.
        if ($owner instanceof PostBy) {
            // Example
            // Group: will return true only if current user is member.
            // Page: will always return true.
            // User: will always return true.
            if ($owner->checkPostBy($user, $content)) {
                return true;
            }

            if (UserPrivacy::hasAccess($user, $owner, 'feed.share_on_wall')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User         $user
     * @param array<mixed> $privacyIds
     * @param int          $privacy
     *
     * @return bool
     */
    private function checkHasAbility(User $user, array $privacyIds, int $privacy): bool
    {
        return DB::table('core_privacy_members as member')
            ->join('core_privacy as privacy', function (JoinClause $join) use ($user) {
                $join->on('member.privacy_id', '=', 'privacy.privacy_id');
                $join->where('member.user_id', '=', $user->entityId());
            })
            ->whereIn('privacy.privacy_id', $privacyIds)
            ->where('privacy', '=', $privacy)
            ->exists();
    }
}
