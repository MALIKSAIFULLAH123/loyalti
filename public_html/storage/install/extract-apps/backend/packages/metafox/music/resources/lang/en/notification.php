<?php

/* this is auto generated file */
return [
    'user_commented_on_your_item_type_in_owner_type' => '<b>{user}</b> commented on {is_themselves, select, 1{{user_name}} other{<b>{user_name}\'s</b>}} {item_type, select, music_song{song} music_album{album music} other{playlist music}} in {owner_type}: <b>{owner_name}</b>.',
    'user_commented_on_your_item_type_title'         => '<b>{user}</b> commented on {is_themselves, select, 1{{user_name}} other{<b>{user_name}\'s</b>}} {item_type, select, music_song{song} music_album{album music} other{playlist music}}: <b>:title</b>.',
    'user_name_created_a_new_album_music_title'      => '<b>{user_name}</b> created a new album music <b>{title}</b>.',
    'user_reacted_to_your_item_type'                 => '<b>{user}</b> reacted to your {item_type, select, music_song{song} music_album{album music} other{playlist music}}: "{title}".',
    'user_reacted_to_your_item_type_in_name'         => '<b>{user}</b> reacted to your {item_type, select, music_song{song} music_album{album music} other{playlist music}} in <b>{owner_name}</b>: "{content}".',
];
