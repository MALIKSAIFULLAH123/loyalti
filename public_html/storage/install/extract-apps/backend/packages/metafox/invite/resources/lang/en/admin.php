<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

return [
    'show_invite_code_on_signup_form_label'            => 'Show Invite Code field on Signup',
    'show_invite_code_on_signup_form_desc'             => 'Enable this option to display the Invite Code field on the signup process.',
    'enable_check_duplicate_invite_label'              => 'Check Duplicate Invites',
    'enable_check_duplicate_invite_desc'               => 'Do you want the site to check for duplicate invites before sending mail invites? This can avoid spamming (userA, userB and userC know personA, they all 3 send an invite so personA receives 3 emails) but can also slow down a little the processs',
    'invite_link_expire_days_label'                    => 'How long will the invite link expire? (days)',
    'invite_link_expire_days_desc'                     => 'Set to 0 for this setting to be inactive. By the fault value is 0. How many days is an invite valid for?',
    'invite_only_label'                                => 'Invite Only',
    'invite_only_desc'                                 => 'Enable this option if your community is an "invite only" community.',
    'make_invited_users_friends_with_their_host_label' => 'Make invited users friends with their host',
    'make_invited_users_friends_with_their_host_desc'  => 'When a user invites aPerson and aPerson becomes a member, should they be made friends right then?',
    'auto_approve_user_registered_label'               => 'Automatically approve registered users',
    'auto_approve_user_registered_desc'                => 'Users registering through invitation codes or links can be automatically approved.',
];
