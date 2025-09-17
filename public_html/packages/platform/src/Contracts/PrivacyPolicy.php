<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface PrivacyPolicy
{
    /**
     * Check permission on resource.
     *
     * @param  User|null $user
     * @param  Entity    $content
     * @return bool
     */
    public function checkPermission(?User $user, Entity $content): bool;

    /**
     * @param  User $user
     * @param  User $owner
     * @return bool
     */
    public function checkBlockUser(User $user, User $owner): bool;

    /**
     * @param  User       $user
     * @param  User       $owner
     * @param  HasPrivacy $content
     * @param  array|null $privacyIds
     * @return bool
     */
    public function checkItemPrivacy(User $user, User $owner, HasPrivacy $content, ?array $privacyIds = null): bool;

    /**
     * Check permission on owner.
     *
     * Example group has privacy.
     *
     * @param User  $user
     * @param ?User $owner
     * @param bool  $cache
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function checkPermissionOwner(User $user, ?User $owner, bool $cache = true): bool;

    /**
     * Check user has ability on owner.
     *
     * @param  User        $user
     * @param  User        $owner
     * @param  int         $privacy
     * @param  string|null $privacyType
     * @return bool
     */
    public function hasAbilityOnOwner(User $user, User $owner, int $privacy, string $privacyType = null): bool;

    /**
     * Check an User can post on another user.
     *
     * @param  User $user
     * @param  User $owner
     * @return bool
     */
    public function checkCreateOnOwner(User $user, User $owner): bool;

    /**
     * Check an User can post an specific resource on another user.
     *
     * @param  Content $content
     * @return bool
     */
    public function checkCreateResourceOnOwner(Content $content): bool;

    /**
     * Get privacy list of a resource.
     *
     * If model implement HasResourceStream it will use its own stream. Otherwise it will use global stream.
     *
     * @param  Content $content
     * @return int[]
     * @see HasResourceStream
     */
    public function getResourcePrivacyList(Content $content): array;

    /**
     * Get privacy item by privacy ids.
     *
     * @param  Content              $content
     * @return array<string, mixed>
     */
    public function getPrivacyItem(Content $content): array;
}
