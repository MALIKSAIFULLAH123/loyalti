<?php

/* this is auto generated file */
return [
    'add_a_live_video' => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select, 0 {{isStreaming, plural, =1{is live now} other {was live}} in <profile>profile</profile>} other {<profile>profile</profile>}}}
                page {{isShared, select, 0 {{isStreaming, plural, =1{is live now} other {was live}} in <profile>profile</profile>} other {<profile>profile</profile>}}}
                other {{isStreaming, plural, =1{is live now} other {was live}}}
        }}

        other {{isShared, select,
            1 {{parentType, select,
                0 {{isStreaming, plural, =1{is live now} other {was live}}}
                other {}
            }}
            other {{isStreaming, plural, =1{is live now} other {was live}}}
        }}
    }',
    'add_new_live_video'                                                     => 'Add New Livestream',
    'all_live_videos'                                                        => 'All Live Videos',
    'all_videos'                                                             => 'All Videos',
    'are_you_sure_you_want_to_end_live_video'                                => 'Are you sure you want to end live video?',
    'camera'                                                                 => 'Camera',
    'connect_devices_to_go_live'                                             => 'Connect devices to go live.',
    'connect_streaming_software_to_go_live'                                  => 'Connect streaming software to go live.',
    'create_live_video'                                                      => 'Create Live Video',
    'featured_videos'                                                        => 'Featured Videos',
    'global_search_live_video_no_result'                                     => 'No live videos are found.',
    'go_live'                                                                => 'Go live',
    'invalid_stream_key'                                                     => 'Invalid stream key.',
    'is_replied'                                                             => 'is replied',
    'is_watching'                                                            => 'is watching',
    'live'                                                                   => 'Live',
    'live_now'                                                               => 'Live now',
    'live_video'                                                             => 'Live Video',
    'live_video_approved_successfully_notification'                          => 'An admin approved your Live Video.',
    'live_video_comments_will_appear_here'                                   => 'Live video comments will appear here.',
    'live_video_had_ended'                                                   => 'Live Video Had Ended',
    'live_video_has_ended_soon_can_not_post_comments_and_likes'              => 'Live video has ended soon can not post comments and likes.',
    'live_video_has_reached_the_limit_time'                                  => 'Live video has reached the limit time.',
    'live_video_is_on_going_are_you_sure_to_leave_now'                       => 'Live video is on going. Are you sure to leave now?',
    'live_video_is_pending_approval'                                         => 'Live video is pending for approval.',
    'live_videos'                                                            => 'Live Videos',
    'manage_chat'                                                            => 'Manage Chat',
    'microphone'                                                             => 'Microphone',
    'no_livestreams_found_description'                                       => 'Begin live streaming to engage and interact with people.',
    'no_live_videos_are_found'                                               => 'No live videos are found.',
    'no_live_videos_description'                                             => 'Try going live video to share for everyone to watch together.',
    'popular_videos'                                                         => 'Popular Videos',
    'recorded_live'                                                          => 'Recorded live',
    'replied_to'                                                             => '{from_name} replied to {to_name}',
    'replying_to_user'                                                       => 'Replying to {user_name}',
    'resource_name_lower_case_live_video'                                    => 'live video',
    'search_live_videos'                                                     => 'Search live videos',
    'sponsored_live_videos'                                                  => 'Sponsored Live Videos',
    'streaming_software'                                                     => 'Streaming Software',
    'the_live_video_will_end_in_n_minutes'                                   => 'The live video will end in {value, plural, =1{# minute} other {# minutes}}.',
    'timeout'                                                                => 'Timeout',
    'total_value_live_videos'                                                => '{value, plural, =1{# live video} other{# live videos} }',
    'total_viewer'                                                           => '{ value, plural, =1{# viewer} other{# viewers} }',
    'user_joined'                                                            => '{user_name} joined',
    'video_unavailable_to_play'                                              => 'Video Unavailable To Play',
    'watch_live_video'                                                       => 'Watch live video',
    'watch_video'                                                            => 'Watch video',
    'webcam'                                                                 => 'Webcam',
    'your_live_video_could_not_been_processed_because_of_network_connection' => 'Your live video could not been processed because of network connection.',
    'your_live_video_had_been_processed_successfully'                        => 'Your live video had been processed successfully.',
    'your_live_video_has_been_processing'                                    => 'Your live video has been processing.',
    'your_live_video_has_ended'                                              => 'Your live video has ended.',
    'your_live_video_has_interrupted'                                        => 'Your live video has interrupted.',
];
