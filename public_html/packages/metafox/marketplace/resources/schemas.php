<?php

/* this is auto generated file */
return [
    [
        'name'      => 'marketplace.marketplace.view_detail',
        'structure' => [
            "@context"    => "https://schema.org",
            "@type"       => "Product",
            "name"        => "{title}",
            "description" => "{description}",
            "image"       => '{attach_photos}',
            "url"         => '{url}',
            "offers"      => [
                "@type"         => "Offer",
                "availability"  => "https://schema.org/InStock",
                "price"         => '{price}',
                "priceCurrency" => '{price_currency}',
            ],
        ],
    ],
    [
        'name'      => 'user.profile.marketplace',
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
                        "@id"  => "{url}/marketplace",
                        "name" => "marketplace::seo.marketplace_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.marketplace',
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
                        "@id"  => "{url}/marketplace",
                        "name" => "marketplace::seo.marketplace_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.marketplace',
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
                        "@id"  => "{url}/marketplace",
                        "name" => "marketplace::seo.marketplace_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
