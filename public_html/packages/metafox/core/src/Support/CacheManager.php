<?php

namespace MetaFox\Core\Support;

class CacheManager
{
    public const SITE_SETTING_CACHE      = 'site_setting_cache';
    public const SITE_SETTING_CACHE_TIME = 3000;

    public const CORE_CURRENCY_CACHE       = 'core_currencies';
    public const CORE_LANGUAGE_CACHE       = 'core_language';
    public const CORE_TIMEZONE_CACHE       = 'core_timezone';
    public const CORE_ATTACHMENT_FILE_TYPE = 'core_attachment_file_type';
    public const CORE_COUNTRY_CACHE        = 'core_country';
    public const CORE_COUNTRY_STATE_CACHE  = 'core_country_state';
    public const CORE_COUNTRY_CITY_CACHE   = 'core_country_city';

    public const CORE_SITE_SETTING_CACHE_VALUE_BAG      = 'core_site_setting_cache_value_bag';
    public const CORE_SITE_SETTING_CACHE_VALUE_BAG_TIME = 3000;

    public const CORE_PACKAGE_GET_ALL          = 'core.module.getAll';
    public const CORE_PACKAGE_GET_UPLOADED     = 'core.module.getUploaded';
    public const CORE_PACKAGE_GET_PURCHASED    = 'core.module.getPurchased';
    public const CORE_PACKAGE_GET_OPTIONS      = 'core.module.getOptions';
    public const CORE_PACKAGE_CACHE_TIME       = 86400;
    public const CORE_RESOURCE_GET_OPTIONS     = 'core.module.getResourceOptions';
    public const CORE_RESOURCE_CACHE_TIME      = 86400;
    public const CORE_MENU_GET_OPTIONS         = 'core.menu.getMEnuOptions';
    public const CORE_MENU_CACHE_TIME          = 86400;
    public const CORE_STATS_CONTENT_TYPE_CACHE = 'core_stats_content_type_cache';
}
