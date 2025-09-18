<?php

/* this is auto generated file */
return [
    [
        'name'      => 'video.video.view_detail',
        'structure' => [
            "@context"             => "https://schema.org",
            "@type"                => "VideoObject",
            "name"                 => "{title}",
            "description"          => "{description}",
            "thumbnailUrl"         => '{image}',
            "uploadDate"           => "{creation_date}",
            "duration"             => "{duration_iso}",
            "contentUrl"           => "{url}",
            "embedUrl"             => "{embed_code}",
            "interactionStatistic" => '{interaction_statistic}',
        ],
    ],
    [
        'name'      => 'user.profile.video',
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
                        "@id"  => "{url}/video",
                        "name" => "video::seo.video_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.video',
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
                        "@id"  => "{url}/video",
                        "name" => "video::seo.video_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.video',
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
                        "@id"  => "{url}/video",
                        "name" => "video::seo.video_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
