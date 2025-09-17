<?php

/* this is auto generated file */

return [
    [
        'name'      => 'music.music_song.view_detail',
        'structure' => [
            "@context"             => "http://schema.org",
            "@type"                => "MusicRecording",
            'datePublished'        => '{creation_date}',
            "description"          => '{description}',
            "name"                 => '{name}',
            "duration"             => '{duration_iso}',
            "inAlbum"              => [
                "@type" => "MusicAlbum",
                "@id"   => "{album_url}",
                'name'  => '{album_name}',
                'image' => '{album_image}',
                'genre' => '{album_genre_names}',
            ],
            "author"               => [
                "@type" => "Person",
                "name"  => '{user_full_name}',
            ],
            "interactionStatistic" => [
                "@type"                => "InteractionCounter",
                "interactionType"      => 'https://schema.org/ListenAction',
                "userInteractionCount" => '{total_play}',
            ],
        ],
    ],
    [
        'name'      => 'music.music_album.view_detail',
        'structure' => [
            "@context" => "http://schema.org",
            "@type"    => "MusicAlbum",
            "@id"      => "{url}",
            'name'     => '{name}',
            'image'    => '{image}',
            'genre'    => '{genre_names}',
            "author"   => [
                "@type" => "Person",
                "name"  => '{user_full_name}',
            ],
            'track'    => '{structure_tracks}',
        ],
    ],
    [
        'name'      => 'music.music_playlist.view_detail',
        'structure' => [
            "@context"      => "http://schema.org",
            "@type"         => "MusicPlaylist",
            'numTracks'     => '{total_song}',
            'datePublished' => '{creation_date}',
            "description"   => '{description}',
            "name"          => '{name}',
            "url"           => '{url}',
            "author"        => [
                "@type" => "Person",
                "name"  => '{user_full_name}',
            ],
            'track'         => [
                '@type'    => 'MusicRecording',
                "inAlbum"  => '{songs.album_name}',
                "duration" => '{songs.duration_iso}',
                "name"     => '{songs.name}',
                "url"      => '{songs.url}',
            ],
        ],
    ],
    [
        'name'      => 'page.profile.music',
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
                        "@id"  => "{url}/music",
                        "name" => "music::seo.page_profile_music_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'group.profile.music',
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
                        "@id"  => "{url}/music",
                        "name" => "music::seo.group_profile_music_title",
                    ],
                ],
            ],
        ],
    ],
    [
        'name'      => 'user.profile.music',
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
                        "@id"  => "{url}/music",
                        "name" => "music::seo.music_song_landing_title",
                    ],
                ],
            ],
        ],
    ],
];
