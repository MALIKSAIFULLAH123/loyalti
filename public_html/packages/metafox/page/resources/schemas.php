<?php

/* this is auto generated file */
return [
    [
        'name'      => 'page.profile.about',
        'structure' => [
            "@context"     => "http://schema.org",
            "@type"        => "ProfilePage",
            'dateCreated'  => '{creation_date}',
            'dateModified' => '{modification_date}',
            "mainEntity"   => [
                "@type"                => "Person",
                "name"                 => '{title}',
                "identifier"           => '{id}',
                "description"          => '{description}',
                "image"                => '{avatar}',
                "sameAs"               => '{external_link}',
                "url"                  => '{url}',
                'interactionStatistic' => [
                    [
                        "@type"                => "InteractionCounter",
                        "interactionType"      => "https://schema.org/FollowAction",
                        "userInteractionCount" => "{total_follower}",
                    ], [
                        "@type"                => "InteractionCounter",
                        "interactionType"      => "https://schema.org/LikeAction",
                        "userInteractionCount" => "{total_like}",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.home',
        'structure' => [
            "@context"     => "http://schema.org",
            "@type"        => "ProfilePage",
            'dateCreated'  => '{creation_date}',
            'dateModified' => '{modification_date}',
            "mainEntity"   => [
                "@type"                => "Person",
                "name"                 => '{title}',
                "identifier"           => '{id}',
                "description"          => '{description}',
                "image"                => '{avatar}',
                "sameAs"               => '{external_link}',
                "url"                  => '{url}',
                'interactionStatistic' => [
                    [
                        "@type"                => "InteractionCounter",
                        "interactionType"      => "https://schema.org/FollowAction",
                        "userInteractionCount" => "{total_follower}",
                    ], [
                        "@type"                => "InteractionCounter",
                        "interactionType"      => "https://schema.org/LikeAction",
                        "userInteractionCount" => "{total_like}",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.member',
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
                        "@id"  => "{url}/member",
                        "name" => "page::seo.page_profile_member_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'user.profile.page',
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
                        "@id"  => "{url}/page",
                        "name" => "page::seo.user_profile_page_title",
                    ],
                ],
            ],
        ],
    ],
];
