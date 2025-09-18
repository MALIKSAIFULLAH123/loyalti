<?php

/* this is auto generated file */

return [
    [
        'name'      => 'quiz.quiz.view_detail',
        'structure' => [
            "@context" => "http://schema.org",
            "@type"    => "Quiz",
            'name'     => '{title}',
            'about'    => [
                "@type" => "Thing",
                'name'  => '{description}',
            ],
            "hasPart"  => '{structured_has_part}',
        ],
    ],
    [
        'name'      => 'user.profile.quiz',
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
                        "@id"  => "{url}/quiz",
                        "name" => "quiz::seo.quiz_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.quiz',
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
                        "@id"  => "{url}/quiz",
                        "name" => "quiz::seo.quiz_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.quiz',
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
                        "@id"  => "{url}/quiz",
                        "name" => "quiz::seo.quiz_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
