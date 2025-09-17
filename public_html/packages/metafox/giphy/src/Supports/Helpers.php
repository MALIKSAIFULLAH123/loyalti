<?php

namespace MetaFox\Giphy\Supports;

class Helpers
{
    public const GIPHY_PUBLIC_API_URL = 'https://api.giphy.com/v1/gifs';

    public const GIPHY_STICKERS_API_URL = 'https://api.giphy.com/v1/stickers';

    public const GIPHY_CLIPS_API_URL = 'https://api.giphy.com/v1/clips';

    public const DEFAULT_LIMIT = 25;

    public const DEFAULT_OFFSET = 0;

    public const GIPHY_LANGUAGES = [
        'en', 'es', 'pt', 'id', 'fr', 'ar', 'tr', 'th', 'vi', 'de', 'it', 'ja',
        'zh-CN', 'zh-TW', 'ru', 'ko', 'pl', 'nl', 'ro', 'hu', 'sv', 'cs', 'hi',
        'bn', 'da', 'fa', 'tl', 'fi', 'iw', 'ms', 'no', 'uk',
    ];

    public const GIPHY_RATINGS = [
        'g',
        'pg',
        'pg-13',
        'r',
    ];

    public const GIPHY_BUNDLES = [
        'clips_grid_picker',
        'messaging_non_clips',
        'sticker_layering',
        'low_bandwidth',
    ];
}
