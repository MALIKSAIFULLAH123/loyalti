<?php

/* this is auto generated file */
return [
    [
        'name'         => 'admin.search.search_setting',
        'phrase_title' => 'core::phrase.settings',
    ],
    [
        'name'               => 'search.search.landing',
        'phrase_title'       => 'search::seo.browse_search_title',
        'phrase_description' => 'search::seo.browse_search_description',
        'phrase_keywords'    => 'search::seo.browse_search_keywords',
        'phrase_heading'     => 'search::seo.browse_search_heading',
        'url'                => 'search',
    ],
    [
        'name'                 => 'search.search.search_landing_by_type',
        'phrase_title'         => 'search::seo.browse_search_title',
        'url'                  => 'search/{filterType}',
        'custom_sharing_route' => 1,
    ],
    [
        'name'               => 'search.search.search_hashtag_landing',
        'phrase_title'       => 'search::seo.browse_search_hashtag_title',
        'phrase_description' => 'search::seo.search_hashtag_landing_description',
        'phrase_keywords'    => 'search::seo.search_hashtag_landing_keywords',
        'phrase_heading'     => 'search::seo.search_hashtag_landing_heading',
        'url'                => 'hashtag/search',
    ],
    [
        'name'           => 'admin.search.update_index',
        'phrase_title'   => 'search::phrase.reindexing',
        'phrase_heading' => 'search::phrase.reindexing',
        'url'            => 'search/reindex/create',
    ],
];
