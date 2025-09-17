<?php

/* this is auto generated file */
return [
    'cache_rand_list_of_friends'             => 'Cached Time of Friend List block',
    'cache_rand_list_of_friends_description' => 'Define how often (in minutes) the Friend List block on the User Profile page is updated.',
    'days_to_check_for_birthday'             => 'How many days in advance to check for birthdays?',
    'days_to_check_for_birthday_description' => 'This setting tells how many days in advance should the script check for.
Setting it to a number too high may beat the purpose of the feature.
The results from this feature cannot be cached, so it is prone to becoming a slow down for your site.
Keep in mind that you can disable this feature all together in the setting "Enable Birthday Notices".',
    'enable_birthday_notices'              => 'Enable Birthday Notices',
    'enable_birthday_notices_description'  => 'When enabled users will see a list of their friends upcoming birthdays.',
    'enable_friend_suggestion'             => 'Friend Suggestions',
    'enable_friend_suggestion_description' => 'Enable this setting if you want to suggest friends to your members when they visit their dashboard.
You can control the search criteria for friend suggestions.
This feature requires a lot of extra server resources in order to perform such a search.
Each search result is cached for X minutes (where you can control X).
Notice: This feature is experimental and is not stable.',
    'friend_cache_limit'             => 'Cache limit for Friend list',
    'friend_cache_limit_description' => 'Certain features on the site pick up on the users\' friend list, especially when running a search for a friend.
In order to provide a "live" feel to search results, we cache in advance X (where X is this setting value) friends in memory to help users find their friends instantly.',
    'friend_display_limit_desc'        => 'Define how many friends should be displayed on a users profile and dashboard.',
    'friend_display_limit_label'       => 'Friends Display Limit',
    'friendship_direction'             => 'Friendship Direction',
    'friendship_direction_desc'        => 'Your social network can allow either one-way or two-way friendships. The default setting is two-way friendships as this is typical for most social networks. One-way friendships mean that when Member A adds Member B, Member B will appear on Member A\'s friend list but NOT the reverse.',
    'friends_only_profile'             => 'Friends Only Profile',
    'friends_only_profile_description' => 'With this setting enabled, only friends can view each other\'s profiles.
Note this setting will override user\'s privacy settings for viewing their profiles to "Friends Only".',
    'friend_suggestion_friend_check_count'                 => 'Friends Suggestion Friends Check Count',
    'friend_suggestion_friend_check_count_desc'            => 'When listing friend suggestions for your members it will pull out X amount of users, where X is the numerical value of how many friends to list.',
    'friend_suggestion_timeout'                            => 'Refresh Friend Suggestions',
    'friend_suggestion_timeout_description'                => 'Define the interval (in minutes) to search and suggest friends to users.',
    'friend_suggestion_based_on_user_location'             => 'Check Location for Friend Suggestions',
    'friend_suggestion_based_on_user_location_description' => 'Enable this option in order for us to pick up friend suggestions for your members based on the Country, State/Province and City they live in.',
    'one_way_friendships'                                  => 'One-way Friendships',
    'two_way_friendships'                                  => 'Two-way Friendships',
    'maximum_name_length_label'                            => 'Maximum Length for Friend List Title',
];
