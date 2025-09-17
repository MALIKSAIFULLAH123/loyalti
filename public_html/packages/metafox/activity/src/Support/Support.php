<?php

namespace MetaFox\Activity\Support;

use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;

class Support
{
    public const SHARED_TYPE = 'wall';

    public const ACTIVITY_SUBSCRIPTION_VIEW_SUPER_ADMIN_FEED = 'view_super_admin_feed';

    public const FEED_SORT_RECENT = Browse::SORT_RECENT;

    public const TOP_STORIES_COMMENT = 'comment';
    public const TOP_STORIES_LIKE    = 'like';
    public const TOP_STORIES_ALL     = 'all';

    /**
     * @return array
     */
    public static function getItemStatuses(): array
    {
        return [MetaFoxConstant::ITEM_STATUS_APPROVED, MetaFoxConstant::ITEM_STATUS_PENDING, MetaFoxConstant::ITEM_STATUS_DENIED, MetaFoxConstant::ITEM_STATUS_REMOVED];
    }

    public static function getTopStoriesUpdateOptions(): array
    {
        return [
            ['label' => __p('activity::admin.top_stories_both'), 'value' => self::TOP_STORIES_ALL],
            ['label' => __p('activity::admin.top_stories_comment'), 'value' => self::TOP_STORIES_COMMENT],
            ['label' => __p('activity::admin.top_stories_like'), 'value' => self::TOP_STORIES_LIKE],
        ];
    }

    public static function getSortOptions(): array
    {

        return [
            [
                'value'       => SortScope::SORT_DEFAULT,
                'label'       => __p('activity::phrase.newest_posts'),
                'description' => __p('activity::web.sort_newest_posts_description'),
            ],
            [
                'value'       => SortScope::SORT_NEWEST_ACTIVITY,
                'label'       => __p('activity::phrase.newest_activity'),
                'description' => __p('activity::web.sort_newest_activity_description'),
            ],
        ];
    }
}
