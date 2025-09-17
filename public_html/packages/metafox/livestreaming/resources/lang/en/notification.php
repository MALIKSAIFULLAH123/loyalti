<?php

/* this is auto generated file */
return [
    'user_commented_on_your_live_video'                            => '<b>{user}</b> commented on {is_themselves, select, 1{{user_name}} other{<b>{user_name}\'s</b>}} {isTitle, select, 1{live video: <b>{title}</b>} other{live video}}.',
    'user_full_name_started_a_live_video_in_owner'                 => '<b>:user_full_name</b> started a live video in :owner_type <b>:owner_name</b>.',
    'username_tagged_you_in_a_live_video'                          => '<b>:username</b> tagged you in a live video.',
    'user_reacted_to_live_video_that_you_are_tagged_in_owner_name' => '<b>{user}</b> { isTitle, select, 1 {reacted to a live video that you are tagged in <b>:owner_name</b>: <b>:feed_content</b>.} other {reacted to a live video that you are tagged in <b>:owner_name</b>.}}',
    'user_reacted_to_live_video_you_are_tagged'                    => '<b>{user}</b> { isTitle, select, 1 {reacted to a live video you are tagged in: <b>:title</b>.} other {reacted to a live video you are tagged in.}}',
    'user_reacted_to_your_live_video'                              => '<b>{user}</b> reacted to your {isTitle, select, 1{live video: <b>{title}</b>} other{live video}}.',
];
