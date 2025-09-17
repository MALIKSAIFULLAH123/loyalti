<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

/**
 * Class MetaFoxPrivacy.
 */
class MetaFoxPrivacy
{
    public const EVERYONE           = 0;
    public const MEMBERS            = 1;
    public const FRIENDS            = 2;
    public const FRIENDS_OF_FRIENDS = 3;
    public const ONLY_ME            = 4;
    public const CUSTOM             = 10;

    public const ID_EVERYONE           = 1;
    public const ID_NETWORK_MEMBER     = 2;
    public const ID_FRIENDS_OF_FRIENDS = 3;

    public const PRIVACY_NETWORK_ITEM_TYPE         = 'sitewide';
    public const PRIVACY_NETWORK_ITEM_ID           = 0;
    public const PRIVACY_NETWORK_PUBLIC            = 'sitewide_public';
    public const PRIVACY_NETWORK_MEMBER            = 'member';
    public const PRIVACY_NETWORK_FRIEND_OF_FRIENDS = 'friend_of_friends';

    public const NETWORK_PUBLIC_PRIVACY_ID    = 1;
    public const NETWORK_MEMBERS_PRIVACY_ID   = 2;
    public const NETWORK_FRIEND_OF_FRIENDS_ID = 3;

    public const PRIVACY_PUBLIC_ICON                    = 'ico-globe-o';
    public const PRIVACY_MEMBERS_ICON                   = 'ico-user-circle';
    public const PRIVACY_FRIENDS_ICON                   = 'ico-user-two-men';
    public const PRIVACY_FRIENDS_OF_FRIENDS_ICON        = 'ico-user-man-three';
    public const PRIVACY_ONLY_ME_ICON                   = 'ico-lock';
    public const PRIVACY_CUSTOM_ICON                    = 'ico-gear';
    public const PRIVACY_PUBLIC_MOBILE_ICON             = 'globe';
    public const PRIVACY_MEMBERS_MOBILE_ICON            = 'user-circle';
    public const PRIVACY_FRIENDS_MOBILE_ICON            = 'user-two-men';
    public const PRIVACY_FRIENDS_OF_FRIENDS_MOBILE_ICON = 'user1-three';
    public const PRIVACY_ONLY_ME_MOBILE_ICON            = 'lock';
    public const PRIVACY_CUSTOM_MOBILE_ICON             = 'gear';

    /**
     * @return array<int, string>
     */
    public static function getPrivacy(): array
    {
        return [
            self::EVERYONE           => 'phrase.privacy.everyone',
            self::MEMBERS            => 'phrase.user_privacy.community',
            self::FRIENDS            => 'phrase.privacy.friends',
            self::FRIENDS_OF_FRIENDS => 'phrase.privacy.friends_of_friends',
            self::ONLY_ME            => 'phrase.privacy.only_me',
            self::CUSTOM             => 'phrase.privacy.custom',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getItemPrivacy(): array
    {
        return [
            self::EVERYONE           => 'phrase.privacy.everyone',
            self::MEMBERS            => 'phrase.user_privacy.community',
            self::FRIENDS            => 'phrase.privacy.friends',
            self::FRIENDS_OF_FRIENDS => 'phrase.privacy.friends_of_friends',
            self::ONLY_ME            => 'phrase.privacy.only_me',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getUserPrivacy(array $excludes = []): array
    {
        $options = [
            self::EVERYONE           => 'phrase.user_privacy.anyone',
            self::MEMBERS            => 'phrase.user_privacy.community',
            self::FRIENDS            => 'phrase.user_privacy.friends_only',
            self::FRIENDS_OF_FRIENDS => 'phrase.privacy.friends_of_friends',
            self::ONLY_ME            => 'phrase.user_privacy.no_one',
        ];

        foreach ($excludes as $exclude) {
            unset($options[$exclude]);
        }

        return $options;
    }
}
