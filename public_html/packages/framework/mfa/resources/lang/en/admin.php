<?php

/* this is auto generated file */
return [
    'enforce_mfa'                     => 'Enforce Multi-Factor Authentication',
    'enforce_mfa_description'         => 'Inform users to enable MFA the first time they log in after this setting is enabled. Users must enable MFA within the set number of days before their accounts get blocked from accessing the site. Disabling this setting will reset all timeout periods previously set for users.',
    'enforce_mfa_timeout'             => 'Timeout Period (days)',
    'enforce_mfa_timeout_description' => 'Define the number of days before the system automatically takes action against users who do not comply with this policy:
- User accounts that signed up using email or phone number will have their MFA method enabled automatically, according to their respective signup method.
- User accounts that signed up without email or phone number will be locked automatically and unable to log in to the site.
Modifying this setting does not affect users who already have their timeout periods set. The value must be greater than 0.',
    'enforce_mfa_targets'             => 'Applicable Targets',
    'enforce_mfa_targets_description' => 'Define the group of users which will be required to enable MFA. You can either apply it to all users or specify user roles. Modifying this setting does not affect users who already have their timeout periods set.',
    'enforce_mfa_roles_description'   => 'Define the roles that users be will be affected by this policy. Modifying this setting does not affect users who already have their timeout periods set.',
];
