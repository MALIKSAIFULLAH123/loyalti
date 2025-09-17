<?php

/* this is auto generated file */
return [
    'added_a_post'                          => 'added a forum post',
    'added_a_thread'                        => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a thread in <profile>profile</profile>}
                }}
                page {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a thread in <profile>profile</profile>}
                }}
                other {added a thread}
        }}

        other {{isShared, select,
            0 {added a thread}
            other {{parentType, select,
                0 {added a thread}
                other {<profile>profile</profile>}
            }}
        }}
    }',
    'all_threads'                           => 'All Threads',
    'communities'                           => 'Communities',
    'community_forums'                      => 'Community Forums',
    'create_new_thread'                     => 'Create New Thread',
    'forums'                                => 'Forums',
    'global_search_forum_thread_no_result'  => 'No threads are found.',
    'last_replied_by_user'                  => 'Last replied by {user}',
    'latest_posts'                          => 'Latest Posts',
    'load_more_forums'                      => 'Load more forums',
    'load_more_threads'                     => 'Load more threads',
    'my_threads'                            => 'My Threads',
    'name_type_forum_thread'                => 'Forum',
    'no_forum_thread_found'                 => 'No threads are found.',
    'no_posts_found'                        => 'No posts are found.',
    'no_threads_found'                      => 'No threads are found.',
    'originally_posted_by'                  => 'Originally posted by',
    'parent_thread'                         => 'Parent Thread',
    'parent_thread_colon'                   => 'Parent Thread:',
    'popular_posts'                         => 'Popular Posts',
    'posted_a_reply_on'                     => 'posted a reply on',
    'posted_by_user'                        => 'Posted by {user}',
    'recent_posts'                          => 'Recent Posts',
    'recent_threads'                        => 'Recent Threads',
    'resource_name_lower_case_forum_post'   => 'reply',
    'resource_name_lower_case_forum_thread' => 'thread',
    'search_discussions'                    => 'Search discussions',
    'search_forums'                         => 'Search forums',
    'search_posts'                          => 'Search posts',
    'search_no_threads_found_desc'          => 'Create a new thread for other people to discussion together.',
    'showing_from_to_of_total_replies'      => 'Showing {from}-{to} of {total} replies',
    'thread_is_waiting_approve'             => 'This thread is waiting for approval',
    'threads'                               => 'Threads',
    'wiki'                                  => 'Wiki',
    'sponsored_posts'                       => 'Sponsored Posts',
    'total_value_threads'                   => '{value, plural, =1{# thread} other{# threads}}',
    'total_value_forum_posts'               => '{value, plural, =1{# post} other{# posts}}',
];
