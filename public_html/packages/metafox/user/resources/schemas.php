<?php

/* this is auto generated file */

return [
    [
        'name'      => 'user.profile.about',
        'structure' => [
            "@context"     => "http://schema.org",
            "@type"        => "ProfilePage",
            'dateCreated'  => '{creation_date}',
            'dateModified' => '{modification_date}',
            "mainEntity"   => [
                "@type"                => "Person",
                "name"                 => '{full_name}',
                "identifier"           => '{id}',
                "description"          => '{bio}',
                "image"                => '{avatar}',
                'interactionStatistic' => [
                    "@type"                => "InteractionCounter",
                    "interactionType"      => "https://schema.org/FollowAction",
                    "userInteractionCount" => "{total_follower}",
                ],
            ],
        ],
    ],
    [
        'name'      => 'user.profile.home',
        'structure' => [
            "@context"     => "http://schema.org",
            "@type"        => "ProfilePage",
            'dateCreated'  => '{creation_date}',
            'dateModified' => '{modification_date}',
            "mainEntity"   => [
                "@type"                => "Person",
                "name"                 => '{full_name}',
                "identifier"           => '{id}',
                "description"          => '{bio}',
                "image"                => '{avatar}',
                'interactionStatistic' => [
                    "@type"                => "InteractionCounter",
                    "interactionType"      => "https://schema.org/FollowAction",
                    "userInteractionCount" => "{total_follower}",
                ],
            ],
        ],
    ],
];
