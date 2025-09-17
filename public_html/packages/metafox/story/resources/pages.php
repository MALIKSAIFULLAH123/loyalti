<?php

return [
    [
        'name'               => 'story.story.landing',
        'phrase_title'       => 'story::seo.story_landing_title',
        'phrase_description' => 'story::seo.story_landing_description',
        'phrase_keywords'    => 'story::seo.story_landing_keywords',
        'phrase_heading'     => 'story::seo.story_landing_heading',
        'url'                => 'story',
        'item_type'          => 'story',
    ],
    [
        'name'         => 'admin.story.story_setting',
        'phrase_title' => 'core::phrase.settings',
        'url'          => 'story/setting',
    ],
    [
        'name'         => 'admin.story.browse_service',
        'phrase_title' => 'story::seo.browse_service',
        'url'          => 'story/service/browse',
    ],
    [
        'name'         => 'admin.story.story_permission',
        'phrase_title' => 'core::phrase.permissions',
        'url'          => 'story/permission',
    ],
    [
        'name'         => 'admin.story.background-set',
        'phrase_title' => 'story::seo.story_background_set_browse_title',
        'url'          => 'story/background-set/browse',
    ],
    [
        'name'         => 'admin.story.background-set.create',
        'phrase_title' => 'story::seo.story_background_set_create_title',
        'url'          => 'story/background-set/create',
    ],
    [
        'name'         => 'admin.story.background-set.edit',
        'phrase_title' => 'story::seo.story_background_set_edit_title',
        'url'          => 'story/background-set/edit/{id}',
    ],
    [
        'name'               => 'story.story.create',
        'phrase_title'       => 'story::seo.story_create_title',
        'phrase_description' => 'story::seo.story_create_description',
        'phrase_keywords'    => 'story::seo.story_create_keywords',
        'phrase_heading'     => 'story::seo.story_create_heading',
        'url'                => 'story/add',
        'item_type'          => 'story',
    ],
    [
        'name'               => 'user.profile.story_archive',
        'phrase_title'       => 'story::seo.user_profile_story_archive_title',
        'phrase_description' => 'story::seo.user_profile_story_archive_description',
        'phrase_keywords'    => 'story::seo.user_profile_story_archive_keywords',
        'phrase_heading'     => 'story::seo.user_profile_story_archive_heading',
        'url'                => 'user/{id}/story-archive',
        'item_type'          => 'user',
    ],
];
