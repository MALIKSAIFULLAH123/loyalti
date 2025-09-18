<?php

/* this is auto generated file */
return [
    'apple_app_id'                   => 'Apple App ID',
    'apple_app_id_description'       => 'Fill in your Apple App ID.',
    'apple_bundle_id'                => 'Apple Bundle ID',
    'apple_bundle_id_description'    => 'Your app Bundle ID on App Store',
    'apple_issuer_id'                => 'Apple Issuer ID',
    'apple_issuer_id_description'    => 'Issuer ID from In-app purchase key you created. You can find it at App Store Connect > Users and Access > Integrations tab > In-app Purchase',
    'apple_key_id'                   => 'Apple Key ID',
    'apple_key_id_description'       => 'Key ID from In-app purchase key you created. You can find it at App Store Connect > Users and Access > Integrations tab > In-app Purchase',
    'apple_private_key'              => 'Apple Private Key',
    'apple_private_key_description'  => 'Private Key from the Key you created before, you can get that key by download your In-app purchase key.',
    'enable_iap_android'             => 'Enable In-App Purchase for Android',
    'enable_iap_android_description' => 'You must upload <a href="{link}">Google Service Account</a> to verify receipt after users buy subscriptions on Android App.

In order to support recurring subscriptions, you must configure Real-time developer notifications on your Google Cloud Console and Google Play Console, please follow step-by-step on this <a href="{android_link}">link</a> to grant permissions and create a new "Topic" in Google Cloud Console (Pub/Sub). You also need to create a  "Subscription" with delivery type is "Push" and add <a href="{webhook}">{webhook}</a> to Endpoint URL.',
    'enable_iap_ios'             => 'Enable In-App Purchase for iOS',
    'enable_iap_ios_description' => 'You must Enabling Server-to-Server Notifications, to work with Auto-renew subscription package on iOS App version.
Follow this <a href="{guideLink}" target="_blank">Link</a> for guide.
Add link <a href="{link}">{link}</a> to Subscription Status URL.
And you must create a key for in-app purchases, system will need that key to verify purchases from Mobile App with App Store. You can follow guide at <a href="{apple_link}" target="_blank">Generate keys for in-app purchases</a>.',
    'enable_iap_sandbox_mode'                 => 'Enable Sandbox for In-App Purchase',
    'enable_iap_sandbox_mode_description'     => 'Sandbox mode is used for testing In-App Purchase. It only works with iOS platform.',
    'google_android_package_name'             => 'Google Package Name',
    'google_android_package_name_description' => 'You can find an app\'s package name in the URL of your app\'s Google Play Store listing. For example, the URL of an app page is play.google.com/store/apps/details?id=com.example.app. The app\'s package name is com.example.app.',
    'google_android_public_key'               => 'Google Play Service - Android Public Key',
    'google_android_public_key_description'   => 'You can find public key of your app at <a href="{link}" target="_blank">Developer Console</a> > Select Your App > Development Tools > Services & APIs.',
    'product_successfully_updated'            => 'Product Successfully Updated.',
    'product_type'                            => 'Product Type',
];
