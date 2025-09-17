<?php

namespace MetaFox\Comment\Support;

use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Browse;

class Helper
{
    public const SORT_ALL    = 'all';
    public const SORT_NEWEST = 'newest';
    public const SORT_OLDEST = 'oldest';

    public const HIDE_OWN    = 'own';
    public const HIDE_GLOBAL = 'global';

    public static function getSortOptions(): array
    {
        return [
            ['value' => 'all', 'label' => __p('comment::web.all_comments'), 'sort_type' => Browse::SORT_TYPE_DESC],
            ['value' => 'newest', 'label' => __p('comment::web.newest'), 'sort_type' => Browse::SORT_TYPE_DESC],
            ['value' => 'oldest', 'label' => __p('comment::web.oldest'), 'sort_type' => Browse::SORT_TYPE_ASC],
        ];
    }

    public static function getReplySortOptions(): array
    {
        return [
            ['value' => 'newest', 'label' => __p('comment::web.newest'), 'sort_type' => Browse::SORT_TYPE_DESC],
            ['value' => 'oldest', 'label' => __p('comment::web.oldest'), 'sort_type' => Browse::SORT_TYPE_ASC],
        ];
    }

    public static function getDefaultReplySortType(): string
    {
        return Helper::getReplySortType(Settings::get('comment.sort_reply_by', Helper::SORT_NEWEST));
    }

    public static function getReplySortType(string $option): string
    {
        $sort = Arr::first(self::getReplySortOptions(), function ($value) use ($option) {
            return Arr::get($value, 'value') == $option;
        }, Browse::SORT_TYPE_DESC);

        if (is_array($sort)) {
            return Arr::get($sort, 'sort_type');
        }

        return Browse::SORT_TYPE_DESC;
    }

    public static function getSortType(string $option): string
    {
        $sort = Arr::first(self::getSortOptions(), function ($value) use ($option) {
            return Arr::get($value, 'value') == $option;
        }, Browse::SORT_TYPE_DESC);

        if (is_array($sort)) {
            return Arr::get($sort, 'sort_type');
        }

        return Browse::SORT_TYPE_DESC;
    }

    public static function getHideTypes(): array
    {
        return [self::HIDE_OWN, self::HIDE_GLOBAL];
    }

    public static function isShowReply(): bool
    {
        return Settings::get('comment.enable_thread') && Settings::get('comment.show_reply');
    }
}
