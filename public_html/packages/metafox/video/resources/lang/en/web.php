<?php

/* this is auto generated file */
return [
    'added_a_video' => '{appName, select,
        feed {{parentType, select,
            0 {added a video}
            other {{isShared, select,
                1 {<profile>profile</profile>}
                other {added a video in <profile>profile</profile>\'s timeline}
            }}
        }}
        other {{isShared, select,
            0 {added a video}
            other {{parentType, select,
                0 {added a video}
                other {<profile>profile</profile>}
            }}
        }}
    }',
    'add_new_video'                   => 'Add New Video',
    'all_videos'                      => 'All Videos',
    'featured_videos'                 => 'Featured Videos',
    'global_search_video_no_result'   => 'No videos are found.',
    'my_pending_videos'               => 'My Pending Videos',
    'my_videos'                       => 'My Videos',
    'name_type_video'                 => 'Videos',
    'no_my_video_found'               => 'No videos are found.',
    'no_videos'                       => 'No videos',
    'no_videos_found'                 => 'No videos are found.',
    'no_videos_found_description'     => 'Share a new video for everyone to watch together.',
    'popular_videos'                  => 'Popular Videos',
    'replay_video'                    => 'Replay video',
    'resource_name_lower_case_video'  => 'video',
    'search_videos'                   => 'Search videos',
    'share_a_video'                   => 'Share A Video',
    'sponsored_videos'                => 'Sponsored Videos',
    'video_failed_status'             => 'Failed',
    'video_has_been_processed_failed' => 'Video has been processed failed',
    'video_is_being_processed'        => 'Video is being processed',
    'video_url'                       => 'Video URL',
    'video_processing_status'         => 'Processing',
    'total_value_videos'              => '{value, plural, =1{# video} other{# videos}}',
    'video_mature_warning_desc'       => 'The video you are about to view may contain nudity, sexual themes, violence/gore, strong language or ideologically sensitive subject matter. Would you like to view this video?',
    'video_mature_warning_title'      => 'Warning!',
];
