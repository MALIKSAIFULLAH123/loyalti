<?php

return [
    [
        'name'         => 'admin.emoney.emoney_setting',
        'phrase_title' => 'core::phrase.settings',
        'url'          => 'ewallet/setting',
    ],
    [
        'name'         => 'admin.emoney.withdraw_request',
        'phrase_title' => 'ewallet::admin.withdraw_requests',
        'url'          => 'ewallet/request/browse',
    ],
    [
        'name'         => 'admin.emoney.exchange_rate',
        'phrase_title' => 'ewallet::admin.exchange_rates',
        'url'          => 'ewallet/exchange-rate/browse',
    ],
    [
        'name'         => 'admin.emoney.conversion_provider',
        'phrase_title' => 'ewallet::admin.conversion_providers',
        'url'          => 'ewallet/conversion-provider/browse',
    ],
    [
        'name'         => 'admin.emoney.withdraw_provider',
        'phrase_title' => 'ewallet::admin.withdrawal_providers',
        'url'          => 'ewallet/withdraw-provider/browse',
    ],
    [
        'name'         => 'admin.emoney.user_balance',
        'phrase_title' => 'ewallet::admin.user_balances',
        'url'          => 'ewallet/user-balance/browse',
    ],
    [
        'name'               => 'emoney.statisic',
        'phrase_title'       => 'ewallet::seo.ewallet_statisic_title',
        'phrase_description' => 'ewallet::seo.ewallet_statisic_description',
        'phrase_keywords'    => 'ewallet::seo.ewallet_statisic_keywords',
        'phrase_heading'     => 'ewallet::seo.ewallet_statisic_heading',
        'url'                => 'ewallet',
    ],
    [
        'name'               => 'emoney.transaction',
        'phrase_title'       => 'ewallet::seo.ewallet_transaction_title',
        'phrase_description' => 'ewallet::seo.ewallet_transaction_description',
        'phrase_keywords'    => 'ewallet::seo.ewallet_transaction_keywords',
        'phrase_heading'     => 'ewallet::seo.ewallet_transaction_heading',
        'url'                => 'ewallet/transaction',
    ],
    [
        'name'         => 'admin.emoney.transaction',
        'phrase_title' => 'ewallet::web.ewallet_transactions',
        'url'          => 'ewallet/transaction/browse',
    ],
    [
        'name'               => 'emoney.request',
        'phrase_title'       => 'ewallet::seo.ewallet_withdrawal_requests_title',
        'phrase_description' => 'ewallet::seo.ewallet_withdrawal_requests_description',
        'phrase_keywords'    => 'ewallet::seo.ewallet_withdrawal_requests_keywords',
        'phrase_heading'     => 'ewallet::seo.ewallet_withdrawal_requests_heading',
        'url'                => 'ewallet/request',
    ],
    [
        'name'                 => 'admin.ewallet.browse_adjustment_history',
        'phrase_title'         => 'ewallet::admin.adjustment_histories',
        'url'                  => 'ewallet/user-balance/{id}/adjustment-history/browse',
        'custom_sharing_route' => 1,
    ],
];
