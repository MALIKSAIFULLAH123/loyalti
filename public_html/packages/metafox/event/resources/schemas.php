<?php

/* this is auto generated file */
return [
    [
        'name'      => 'user.profile.event',
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
                        "@id"  => "{url}/event",
                        "name" => "event::seo.event_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.event',
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
                        "@id"  => "{url}/event",
                        "name" => "event::seo.event_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.event',
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
                        "@id"  => "{url}/event",
                        "name" => "event::seo.event_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'event.event.view_detail',
        'structure' => [
            "@context"    => "https://schema.org",
            "@type"       => "Event",
            "name"        => '{title}',
            "startDate"   => '{start_time}',
            "endDate"     => '{end_time}',
            "location"    => '{structure_location}',
            "image"       => '{image}',
            "description" => '{description}',
            "url"         => '{url}',
            "organizer"   => [
                "@type" => "Person",
                "name"  => '{user_full_name}',
                "url"   => '{user_url}',
            ],
        ],
    ],
];
