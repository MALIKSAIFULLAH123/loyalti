<?php

namespace MetaFox\Group\Support;

use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\PrivacyTypeHandlerInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Class PrivacyTypeHandler.
 */
class PrivacyTypeHandler implements PrivacyTypeHandlerInterface
{
    public const PUBLIC = 0;
    public const CLOSED = 1;
    public const SECRET = 2;

    public const ALLOW_PRIVACY = [
        self::PUBLIC,
        self::CLOSED,
        self::SECRET,
    ];

    public const PRIVACY_PHRASE = [
        self::PUBLIC => 'group::phrase.public',
        self::CLOSED => 'group::phrase.closed',
        self::SECRET => 'group::phrase.secret',
    ];

    private function getPrivacyPolicy(): PrivacyPolicy
    {
        return resolve(PrivacyPolicy::class);
    }

    /**
     * @return array<int, string>
     */
    public function getPrivacyLayerList(): array
    {
        return [
            self::PUBLIC => 'group.anyone_can_see_the_group_its_members_and_their_posts',
            self::CLOSED => 'group.anyone_can_find_the_group_and_see_who_s_in_it_only_members_can_see_posts',
            self::SECRET => 'group.only_members_can_find_the_group_and_see_posts',
        ];
    }

    /**
     * Group public and closed can be find.
     * Group secret is hide.
     *
     * @param User  $user
     * @param Group $group
     *
     * @return bool
     */
    public function checkCanViewGroup(User $user, Group $group): bool
    {
        return $this->getPrivacyPolicy()->checkPermissionOwner($user, $group);
    }

    /**
     * Group public and closed can view members.
     * Group secret is hide.
     *
     * @param User  $user
     * @param Group $group
     *
     * @return bool
     */
    public function checkCanViewMember(User $user, Group $group): bool
    {
        return $this->getPrivacyPolicy()->checkPermissionOwner($user, $group);
    }

    /**
     * Group public can view specific post.
     * Group closed and secret cannot specific post.
     *
     * @param User    $user
     * @param Content $content
     *
     * @return bool
     */
    public function checkCanViewContent(User $user, Content $content): bool
    {
        return $this->getPrivacyPolicy()->checkPermission($user, $content);
    }

    public function getPrivacy(int $typeId): int
    {
        switch ($typeId) {
            case self::SECRET:
                return MetaFoxPrivacy::FRIENDS;
            default:
                return MetaFoxPrivacy::EVERYONE;
        }
    }

    public function getPrivacyItem(int $typeId): int
    {
        switch ($typeId) {
            case self::PUBLIC:
                return MetaFoxPrivacy::EVERYONE;
            default:
                return MetaFoxPrivacy::FRIENDS;
        }
    }
}
