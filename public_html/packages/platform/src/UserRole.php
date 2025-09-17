<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

/**
 * Class UserRole.
 */
class UserRole
{
    /**
     * @var string
     */
    public const SUPER_ADMIN_USER = self::SUPER_ADMIN_USER_ID;

    /**
     * @var string
     */
    public const ADMIN_USER = self::ADMIN_USER_ID;

    /**
     * @var string
     */
    public const STAFF_USER = self::STAFF_USER_ID;

    /**
     * @var string
     */
    public const NORMAL_USER = self::NORMAL_USER_ID;

    public const PAGE_USER = self::PAGE_USER_ID;

    /**
     * @var string
     */
    public const GUEST_USER = self::GUEST_USER_ID;

    /**
     * @var string
     */
    public const BANNED_USER = self::BANNED_USER_ID;

    /**
     * @var int
     */
    public const SUPER_ADMIN_USER_ID = 1;

    /**
     * @var int
     */
    public const ADMIN_USER_ID = 2;

    /**
     * @var int
     */
    public const STAFF_USER_ID = 3;

    /**
     * @var int
     */
    public const NORMAL_USER_ID = 4;

    /**
     * @var int
     */
    public const GUEST_USER_ID = 5;

    /**
     * @var int
     */
    public const BANNED_USER_ID = 6;

    /**
     * @var int
     */
    public const PAGE_USER_ID = 7;

    /**
     * Define default roles when install metafox:install.
     *
     * @var string[]
     */
    public const ROLES = [
        self::SUPER_ADMIN_USER_ID => 'Super Administrator',
        self::ADMIN_USER_ID       => 'Administrator',
        self::STAFF_USER_ID       => 'Staff',
        self::NORMAL_USER_ID      => 'Registered User',
        self::GUEST_USER_ID       => 'Guest User',
        self::BANNED_USER_ID      => 'Banned User',
        self::PAGE_USER_ID        => 'Page User',
    ];

    /**
     * Admin role level. Per app will assign permissions to this role list.
     *
     * @var string[]
     */
    public const LEVEL_ADMINISTRATOR = [
        self::SUPER_ADMIN_USER,
        self::ADMIN_USER,
    ];

    /**
     * Staff role level.
     *
     * @var string[]
     */
    public const LEVEL_STAFF = [
        self::SUPER_ADMIN_USER,
        self::ADMIN_USER,
        self::STAFF_USER,
    ];

    /**
     * Registered user role level.
     *
     * @var string[]
     */
    public const LEVEL_REGISTERED = [
        self::SUPER_ADMIN_USER,
        self::ADMIN_USER,
        self::STAFF_USER,
        self::NORMAL_USER,
    ];

    /**
     * @var string[]
     */
    public const LEVEL_PAGE = [
        self::SUPER_ADMIN_USER,
        self::ADMIN_USER,
        self::STAFF_USER,
        self::NORMAL_USER,
        self::PAGE_USER,
    ];

    /**
     * Guest role level.
     *
     * @var string[]
     */
    public const LEVEL_GUEST = [
        self::SUPER_ADMIN_USER,
        self::ADMIN_USER,
        self::STAFF_USER,
        self::NORMAL_USER,
        self::PAGE_USER,
        self::GUEST_USER,
    ];

    /**
     * Banned user role level.
     *
     * @var string[]
     */
    public const LEVEL_BANNED = [
        self::SUPER_ADMIN_USER,
        self::ADMIN_USER,
        self::STAFF_USER,
        self::NORMAL_USER,
        self::GUEST_USER,
        self::BANNED_USER,
    ];
}
