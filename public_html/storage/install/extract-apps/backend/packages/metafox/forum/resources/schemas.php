<?php

/* this is auto generated file */

return [
    [
        'name'      => 'forum.forum.detail',
        'structure' => [
            "@context"   => "http://schema.org",
            "@type"      => "WebPage",
            "url"        => "{url}",
            "mainEntity" => [
                "@type" => "DiscussionForumPosting",
                "url"   => "{url}",
                "name"  => "{title}",
            ],
        ],
    ],
    [
        'name'      => 'forum.forum_thread.view_detail',
        'structure' => [
            "@context"             => "http://schema.org",
            "@type"                => "DiscussionForumPosting",
            "url"                  => "{url}",
            "headline"             => "{title}",
            "text"                 => "{short_description}",
            "author"               => [
                "@type" => "Person",
                "name"  => "{user_full_name}",
                "url"   => "{user_url}",
            ],
            "datePublished"        => "{creation_date}",
            "interactionStatistic" => [
                "@type"                => "InteractionCounter",
                "interactionType"      => "https://schema.org/LikeAction",
                "userInteractionCount" => "{total_like}",
            ],
            "comment"              => [
                "@type"         => "Comment",
                "text"          => "{posts.short_content}",
                "datePublished" => "{posts.creation_date}",
                "author"        => [
                    "@type" => "Person",
                    "name"  => "{posts.user_full_name}",
                    "url"   => "{posts.user_url}",
                ],
            ],
        ],
    ],
    [
        'name'      => 'user.profile.forum',
        'structure' => [
            "@context"        => "http://schema.org",
            "@type"           => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type"    => "ListItem",
                    "position" => 1,
                    "item"     => [
                        "@id"  => "{url}",
                        "name" => "{full_name}",
                    ],
                ],
                [
                    "@type"    => "ListItem",
                    "position" => 2,
                    "item"     => [
                        "@id"  => "{url}/forum",
                        "name" => "forum::seo.forum_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.forum',
        'structure' => [
            "@context"        => "http://schema.org",
            "@type"           => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type"    => "ListItem",
                    "position" => 1,
                    "item"     => [
                        "@id"  => "{url}",
                        "name" => "{title}",
                    ],
                ],
                [
                    "@type"    => "ListItem",
                    "position" => 2,
                    "item"     => [
                        "@id"  => "{url}/forum",
                        "name" => "forum::seo.forum_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.forum',
        'structure' => [
            "@context"        => "http://schema.org",
            "@type"           => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type"    => "ListItem",
                    "position" => 1,
                    "item"     => [
                        "@id"  => "{url}",
                        "name" => "{title}",
                    ],
                ],
                [
                    "@type"    => "ListItem",
                    "position" => 2,
                    "item"     => [
                        "@id"  => "{url}/forum",
                        "name" => "forum::seo.forum_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
