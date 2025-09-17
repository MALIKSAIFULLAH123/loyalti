<?php

/* this is auto generated file */

return [
    [
        'name'      => 'poll.poll.view_detail',
        'structure' => [
            "@context"   => "http://schema.org",
            "@type"      => "QAPage",
            'mainEntity' => [
                "@type"           => "Question",
                'name'            => '{question}',
                'text'            => '{description}',
                'answerCount'     => '{answer_count}',
                'upvoteCount'     => '{total_vote}',
                'dateCreated'     => '{creation_date}',
                'dateModified'    => '{modification_date}',
                'url'             => '{url}',
                'thumbnailUrl'    => '{image}',
                'author'          => [
                    "@type" => "Person",
                    'name'  => '{user_full_name}',
                    'url'   => '{user_url}',
                ],
                'suggestedAnswer' => "{suggested_answer}",
            ],
        ],
    ],
    [
        'name'      => 'user.profile.poll',
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
                        "@id"  => "{url}/poll",
                        "name" => "poll::seo.poll_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.poll',
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
                        "@id"  => "{url}/poll",
                        "name" => "poll::seo.poll_landing_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'page.profile.poll',
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
                        "@id"  => "{url}/poll",
                        "name" => "poll::seo.poll_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
