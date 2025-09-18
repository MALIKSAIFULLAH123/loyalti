<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

return [
    'user_invites_you_to_site_name'                      => '{user_name} invites you to {site_name}.',
    'to_check_out_this_invitation_follow_the_link_below' => 'To check out this invitation, follow the link below: <br/> {url}',
    'invite_email_html'                                  => 'Hello,<br/><br/>
{user_name} invites you to {site_name}.<br/><br/>
To check out this invitation, follow the link below: <br/> <a href="{url}">{url}</a><br/>
and enter the invitation code:  <b>{invite_code}</b>.
{has_message, select, 0{} other{<br/><br/>{user_name} added the following personal message:<br/> {message}}}',
    'invite_sms_message'                                 => 'Hello, {user_name} invites you to {site_name}.
To check out this invitation, follow the link below: <br/>{url}.
and enter the invitation code:  {invite_code}.
{has_message, select, 0{} other{<br/><br/>{user_name} added the following personal message:<br/> {message}}}',
];
