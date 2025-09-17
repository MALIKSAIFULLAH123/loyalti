<?php

/* this is auto generated file */
return [
    [
        'name'      => 'blog.blog.view_detail',
        'structure' => [
            "@context"      => "http://schema.org",
            "@type"         => "NewsArticle",
            "author"        => [
                [
                    "@type" => "Person",
                    "name"  => '{user_full_name}',
                    "url"   => '{user_url}',
                ],
            ],
            'headline'      => '{title}',
            'genre'         => '{categories.name}',
            'image'         => '{image}',
            'description'   => '{description}',
            "keywords"      => '{tags}',
            'datePublished' => '{creation_date}',
            'dateModified'  => '{modification_date}',
        ],
    ],
    [
        'name'      => 'user.profile.blog',
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
                        "@id"  => "{url}/blog",
                        "name" => "blog::seo.blog_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.blog',
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
                        "@id"  => "{url}/blog",
                        "name" => "blog::seo.blog_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.blog',
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
                        "@id"  => "{url}/blog",
                        "name" => "blog::seo.blog_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
