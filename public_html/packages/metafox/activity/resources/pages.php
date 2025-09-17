<?php

/* this is auto generated file */
return [
    [
        'name'               => 'activity.feed.home',
        'phrase_title'       => 'activity::seo.activity_feed_home_title',
        'phrase_description' => 'activity::seo.activity_feed_home_description',
        'phrase_keywords'    => 'activity::seo.activity_feed_home_keywords',
        'phrase_heading'     => 'activity::seo.activity_feed_home_heading',
    ],
    [
        'name'         => 'admin.activity.activity_setting',
        'phrase_title' => 'core::phrase.settings',
        'url'          => 'activity/setting',
    ],
    [
        'name'         => 'admin.activity.browse_type',
        'phrase_title' => 'activity::phrase.types',
        'url'          => 'activity/type/browse',
    ],
    [
        'name'         => 'admin.activity.browse_feed',
        'phrase_title' => 'activity::phrase.manage_feed',
        'url'          => 'activity/feed/browse',
    ],
    [
        'name'         => 'admin.activity.permissions',
        'phrase_title' => 'core::phrase.permissions',
        'url'          => 'activity/permission',
    ],
    [
        'name'               => 'feed.feed.view_detail',
        'url'                => 'feed/{id}',
        'item_type'          => 'feed',
        'phrase_title'       => 'activity::seo.activity_feed_view_detail_title',
        'phrase_description' => 'activity::seo.activity_feed_view_detail_description',
        'phrase_keywords'    => 'activity::seo.activity_feed_view_detail_keywords',
        'phrase_heading'     => 'activity::seo.activity_feed_view_detail_heading',
    ],
    [
        'name'                 => 'feed.feed.view_detail_on_owner',
        'url'                  => '{ownerType}/{ownerId}/feed/{id}',
        'item_type'            => 'feed',
        'phrase_title'         => 'activity::seo.activity_feed_view_detail_title',
        'custom_sharing_route' => 1,
    ],
    [
        'name'               => 'user.settings.scheduled_post',
        'phrase_title'       => 'activity::seo.activity_feed_scheduled_post_title',
        'phrase_description' => 'activity::seo.activity_feed_scheduled_post_description',
        'phrase_keywords'    => 'activity::seo.activity_feed_scheduled_post_keywords',
        'phrase_heading'     => 'activity::seo.activity_feed_scheduled_post_heading',
        'url'                => 'settings/scheduled-posts',
    ],
];
