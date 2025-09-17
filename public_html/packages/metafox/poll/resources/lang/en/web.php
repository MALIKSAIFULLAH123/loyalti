<?php

/* this is auto generated file */
return [
    'added_a_poll'                   => '{appName, select,
        feed {{
            parentType, select,
                group {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a poll in <profile>profile</profile>}
                }}
                page {{isShared, select,
                    1 {<profile>profile</profile>}
                    other {added a poll in <profile>profile</profile>}
                }}
                other {{isOwner, select, 1 {{fromResource, select, app {added a poll} other {}}} other {added a poll}}}
        }}

        other {{isShared, select,
            0 {added a poll}
            other {{parentType, select,
                0 {added a poll}
                other {<profile>profile</profile>}
            }}
        }}
    }',
    'add_new_poll'                   => 'Add New Poll',
    'add_pool'                       => 'Add New Poll',
    'all_polls'                      => 'All Polls',
    'attach_poll'                    => 'Attach Poll',
    'create_poll'                    => 'Create Poll',
    'edit_poll'                      => 'Edit Poll',
    'expire_on'                      => 'Expires on',
    'featured_polls'                 => 'Featured Polls',
    'global_search_poll_no_result'   => 'No polls are found.',
    'my_pending_polls'               => 'My Pending Polls',
    'my_polls'                       => 'My Polls',
    'name_type_poll'                 => 'Poll',
    'no_all_poll_found'              => 'No polls are found.',
    'no_friend_poll_found'           => 'No polls are found.',
    'no_landing_poll_found'          => 'No polls are found.',
    'no_my_pending_poll_found'       => 'No polls are found.',
    'no_my_poll_found'               => 'No polls are found.',
    'no_pending_poll_found'          => 'No polls are found.',
    'no_poll_found'                  => 'No polls are found.',
    'no_polls_found'                 => 'No polls are found.',
    'no_polls_found_description'     => 'Create a new poll for other people to vote together.',
    'no_votes'                       => '0 Votes',
    'people_who_voted'               => 'People Who Voted',
    'please_select_an_option'        => 'Please select an option',
    'poll_closed'                    => '[CLOSED]',
    'poll_is_waiting_approve'        => 'This poll is waiting for approval.',
    'polls'                          => 'Polls',
    'popular_polls'                  => 'Popular Polls',
    'resource_name_lower_case_poll'  => 'poll',
    'save_to_collection'             => 'Save to collection',
    'search_polls'                   => 'Search polls',
    'sponsored_polls'                => 'Sponsored Polls',
    'value_more_options'             => '{value} more options',
    'voting_for_the_poll_was_closed' => 'Voting for the poll was closed.',
    'time_left'                      => '{time} left',
    'voting_closed'                  => 'Voting Closed',
    'total_value_polls'              => '{value, plural, =1{# poll} other{# polls}}',
];
