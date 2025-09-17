<?php

/* this is auto generated file */
return [
    'photo_approved_successfully_notification'                 => 'An admin approved your Photo.',
    'photo_group_items_read_to_view'                           => 'Your post is ready to view.',
    'photo_group_items_ready_but_pending'                      => 'Your post has done processing! However, it still needs approval from admins.',
    'user_commented_on_owner_album'                            => '<b>{user}</b> commented on <b>:owner\'s</b> album: "{title}".',
    'user_commented_on_owner_photo'                            => '<b>{user}</b> { isTitle, select, 1 {commented on <b>{owner}\'s</b> photo: "{title}".} other {commented on <b>{owner}\'s</b> photo.}}',
    'user_commented_on_post_that_you_are_tagged'               => '<b>{user}</b> commented on a photo that you are tagged in.',
    'user_commented_on_post_that_you_are_tagged_in_owner_name' => '<b>{user}</b> { isTitle, select, 1 {commented on a photo that you are tagged in <b>:owner_name</b>: ":feed_content".} other {commented on a photo that you are tagged in <b>:owner_name</b>.}}',
    'user_commented_on_your_album'                             => '<b>{user}</b> commented on your album: <b>"{title}"</b>.',
    'user_commented_on_your_album_in_owner_type'               => '<b>{user}</b> commented on your album in <b>{owner_type}</b>.',
    'user_commented_on_your_photo'                             => '<b>{user}</b> commented on your photo.',
    'user_commented_on_your_photo_group'                       => '<b>{user}</b> {total_photo, plural, =0{{total_video, plural, =0{} =1{{total_photo, plural, =0{commented on your video.} other{commented on your post.}}} other{commented on your post.}}} =1{{total_video, plural, =0{commented on your photo.} other{commented on your post.}}} other{commented on your post.}}',
    'user_commented_on_your_photo_in_owner_type'               => '<b>{user}</b> commented on {user_name, select, your{your} other{<b>{user_name}\'s</b>}} photo in <b>{owner_name}</b>.',
    'user_name_posted_a_post'                                  => '<b>{user_name}</b> posted a post.',
    'user_name_updated_their_cover_or_profile_picture'         => '<b>{user_name}</b> updated {gender} {type, select, 1 {profile picture} 2{cover photo} other {}}.',
    'user_name_updated_parent_cover_or_profile_picture'        => 'An admin changed the {type, select, 1 {avatar} 2 {cover} other {}} of the {owner_type} <b>{owner_name}\'s</b>.',
    'user_reacted_to_post_that_you_are_tagged_in_owner_name'   => '<b>{user}</b> { isTitle, select, 1 {reacted to a photo that you are tagged in <b>:owner_name</b>: ":feed_content".} other {reacted to a photo that you are tagged in <b>:owner_name</b>.}}',
    'user_reacted_to_post_you_are_tagged'                      => '<b>{user}</b> reacted to a photo you are tagged in.',
    'user_reacted_to_your_album'                               => '<b>{user}</b> reacted to your album: <b>"{title}"</b>.',
    'user_reacted_to_your_album_in_name'                       => '<b>{user}</b> reacted to your album in <b>{owner_name}</b>.',
    'user_reacted_to_your_photo'                               => '<b>{user}</b> reacted to your photo.',
    'user_reacted_to_your_photo_group'                         => '<b>{user}</b> {total_photo, plural, =0{{total_video, plural, =0{} =1{{total_photo, plural, =0{reacted to your video.} other{reacted to your post.}}} other{reacted to your post.}}} =1{{total_video, plural, =0{reacted to your photo.} other{reacted to your post.}}} other{reacted to your post.}}',
    'user_reacted_to_your_photo_in_name'                       => '<b>{user}</b> reacted to your photo in <b>{owner_name}</b>.',
];
