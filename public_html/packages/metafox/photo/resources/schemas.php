<?php

/* this is auto generated file */

$siteUrl = config('app.url');

return [
    [
        'name'      => 'photo.photo.view_detail',
        'structure' => [
            "@context"           => "http://schema.org",
            "@type"              => "ImageObject",
            'datePublished'      => '{creation_date}',
            "description"        => '{description}',
            "name"               => '{title}',
            "contentUrl"         => '{url}',
            "copyrightNotice"    => '{user_full_name}',
            "creditText"         => '{credit_text}',
            "license"            => '{license_url}',
            "acquireLicensePage" => '{acquire_license_url}',
            "creator"            => [
                "@type" => "Person",
                "name"  => '{user_full_name}',
            ],
        ],
    ],
    [
        'name'      => 'photo.photo_album.view_detail',
        'structure' => [
            "@context"      => "http://schema.org",
            "@type"         => "CreativeWork",
            'datePublished' => '{creation_date}',
            "description"   => '{description}',
            "name"          => '{title}',
            "url"           => '{url}',
            "author"        => [
                "@type" => "Person",
                "name"  => '{user_full_name}',
            ],
        ],
    ],
    [
        'name'      => 'page.profile.photo',
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
                        "@id"  => "{url}/photo",
                        "name" => "photo::seo.photo_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.photo',
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
                        "@id"  => "{url}/photo",
                        "name" => "photo::seo.photo_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'user.profile.photo',
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
                        "@id"  => "{url}/photo",
                        "name" => "photo::seo.photo_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
