<?php

namespace MetaFox\User\Support;

class CacheManager
{
    public const USER_PROFILE_CACHE      = 'user_profile';
    public const USER_PROFILE_CACHE_TIME = 3000;

    public const USER_ITEM_PRIVACY_CACHE      = 'user_item_privacy';
    public const USER_ITEM_PRIVACY_CACHE_TIME = 3000;

    public const AUTH_ROLES_CACHE      = 'auth_roles';
    public const AUTH_ROLES_CACHE_TIME = 6000;

    public const AUTH_ROLE_OPTIONS_CACHE = 'RoleRepository::getRoleOptions';

    public const USER_PREFERENCE_VALUE_BY_NAME_CACHE = '%s_type_%s_id_%s';
}
