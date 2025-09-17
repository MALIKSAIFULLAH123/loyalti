<?php

/* this is auto generated file */
return [
    'added_a_song'                                  => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a song in <profile>profile</profile>}
                }}
                page {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a song in <profile>profile</profile>}
                }}
                other {added a song}
        }}

        other {{isShared, select,
            0 {added a song}
            other {{parentType, select,
                0 {added a song}
                other {<profile>profile</profile>}
            }}
        }}
    }',
    'add_new_music'                                 => 'Add New Music',
    'add_new_song'                                  => 'Add New Song',
    'add_some_description_to_your_song'             => 'Add some description to your song',
    'all_playlists'                                 => 'All Playlists',
    'all_songs'                                     => 'All Songs',
    'created_a_music_album'                         => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {created a music album <album_link>album_link</album_link> in <profile>profile</profile>}
                }}
                page {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {created a music album <album_link>album_link</album_link> in <profile>profile</profile>}
                }}
                other {created a music album <album_link>album_link</album_link>}
        }}

        other {{isShared, select,
            0 {created a music album <album_link>album_link</album_link>}
            other {{parentType, select,
                0 {created a music album <album_link>album_link</album_link>}
                other {}
            }}
        }}
    }',
    'detail'                                        => 'Detail',
    'featured_songs'                                => 'Featured Songs',
    'fill_in_a_title_for_your_song'                 => 'Fill in a title for your song',
    'find_your_favorite'                            => 'Find Your Favorite',
    'find_your_favorite_music_and_add_to_your_list' => 'Upload new music for everyone to listen.',
    'genres'                                        => 'Genres',
    'global_search_music_album_no_result'           => 'No music albums are found.',
    'global_search_music_playlist_no_result'        => 'No playlists are found.',
    'global_search_music_song_no_result'            => 'No songs are found.',
    'music'                                         => 'Music',
    'music_albums'                                  => 'Music Albums',
    'music_plays'                                   => 'Plays',
    'musics'                                        => 'Musics',
    'music_song_description'                        => 'Description',
    'music_song_name'                               => 'Name',
    'music_songs'                                   => 'Music Songs',
    'music_song_total_play'                         => '{value, plural, =1{# play} other {# plays}}',
    'my_library'                                    => 'My Library',
    'my_playlists'                                  => 'My Playlists',
    'my_songs'                                      => 'My Songs',
    'next__value_songs'                             => 'NEXT ( {value} SONGS )',
    'no_albums_found_description'                   => 'Create mesmerizing album art to promote your music.',
    'no_playlists_found'                            => 'No playlists are found.',
    'no_playlists_found_description'                => 'Create playlists to organize your music.',
    'no_songs_found_description'                    => 'Upload new music for everyone to listen.',
    'now_playing_list'                              => 'Now playing list',
    'playlist_colon'                                => 'Playlist: ',
    'popular_albums'                                => 'Popular Albums',
    'popular_songs'                                 => 'Popular Songs',
    'resource_name_lower_case_music_song'           => 'song',
    'saved_playlists'                               => 'Saved Playlists',
    'search_songs'                                  => 'Search songs',
    'select_music'                                  => 'Select Music',
    'song'                                          => 'Song',
    'sponsored_albums'                              => 'Sponsored Albums',
    'sponsored_playlists'                           => 'Sponsored Playlists',
    'sponsored_songs'                               => 'Sponsored Songs',
    'time_hr'                                       => 'hr',
    'time_min'                                      => 'min',
    'time_sec'                                      => 'sec',
    'total_song'                                    => '{value, plural, =1 {# song} other {# songs}}',
    'turn_off_repeat'                               => 'Turn off repeat',
    'turn_off_shuffle'                              => 'Turn off shuffle',
    'turn_on_repeat'                                => 'Turn on repeat',
    'turn_on_repeat_one'                            => 'Turn on repeat one',
    'turn_on_shuffle'                               => 'Turn on shuffle',
    'you_have_reached_your_limit'                   => 'You have reached your limit. You are currently unable to post a new {entity_type, select,music_song {song} music_album {music album} music_playlist{playlist} other{}}.',
    'total_value_songs'                             => '{value, plural, =1{# song} other{# songs}}',
    'total_value_playlists'                         => '{value, plural, =1{# playlist} other{# playlists}}',
    'total_value_music_albums'                      => '{value, plural, =1{# music album} other{# music albums}}',
];
