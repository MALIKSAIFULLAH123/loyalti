<?php

/* this is auto generated file */
return [
    [
        'name'      => 'group.profile.home',
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
                "image"                => '{cover}',
                "sameAs"               => '{external_link}',
                "url"                  => '{url}',
                'interactionStatistic' => [
                    "@type"                => "InteractionCounter",
                    "interactionType"      => "https://schema.org/FollowAction",
                    "userInteractionCount" => "{total_member}",
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.member',
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
                        "name" => "group::seo.group_profile_member_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.about',
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
                "image"                => '{cover}',
                "sameAs"               => '{external_link}',
                "url"                  => '{url}',
                'interactionStatistic' => [
                    "@type"                => "InteractionCounter",
                    "interactionType"      => "https://schema.org/FollowAction",
                    "userInteractionCount" => "{total_member}",
                ],
            ],
        ],
    ],
    [
        'name'      => 'user.profile.group',
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
                        "@id"  => "{url}/group",
                        "name" => "group::seo.group_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
