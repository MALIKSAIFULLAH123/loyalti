<?php

return [
    'server'                   => env('MFOX_CHATPLUS_SERVER', ''),
    'private_code'             => env('MFOX_CHATPLUS_PRIVATE_CODE', ''),
    'ios_apn_key'              => env('MFOX_CHATPLUS_IOS_APN_KEY', ''),
    'ios_apn_key_id'           => env('MFOX_CHATPLUS_IOS_APN_KEY_ID', ''),
    'ios_apn_team_id'          => env('MFOX_CHATPLUS_IOS_APN_TEAM_ID', ''),
    'jitsi_enable_auth'        => env('MFOX_CHATPLUS_JITSI_ENABLE_AUTH', false),
    'jitsi_domain_option'      => env('MFOX_CHATPLUS_JITSI_DOMAIN_OPTION', 'jitsi'),
    'jitsi_domain'             => env('MFOX_CHATPLUS_JITSI_DOMAIN', ''),
    'jitsi_application_id'     => env('MFOX_CHATPLUS_JITSI_APPLICATION_ID', ''),
    'jitsi_application_secret' => env('MFOX_CHATPLUS_JITSI_APPLICATION_SECRET', ''),
    'firebase_server_key'      => env('MFOX_CHATPLUS_FIREBASE_SERVER_KEY', ''),
    'firebase_sender_id'       => env('MFOX_CHATPLUS_FIREBASE_SENDER_ID', ''),
];
