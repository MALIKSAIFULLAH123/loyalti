<?php
/* this is auto generated file */
return [
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\Admin\\DataGrid',
        'type' => 'data-grid',
        'name' => 'payment.gateway',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Data Grid'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Order\\Admin\\DataGrid',
        'type' => 'data-grid',
        'name' => 'payment.order',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Data Grid Settings',
        'type_label' => 'Data Grid'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Transaction\\Admin\\DataGrid',
        'type' => 'data-grid',
        'name' => 'payment.transaction',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Data Grid Settings',
        'type_label' => 'Data Grid'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Transaction\\Admin\\DetailDataGrid',
        'type' => 'data-grid',
        'name' => 'payment.transaction.detail',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Data Grid Settings',
        'type_label' => 'Data Grid'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Models\\Gateway',
        'type' => 'entity',
        'name' => 'payment.gateway',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\PaymentSettingMobileForm',
        'type' => 'form',
        'name' => 'payment.account.setting',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\Admin\\GatewayForm',
        'type' => 'form',
        'name' => 'payment.gateway.form',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Order\\GatewayForm',
        'type' => 'form',
        'name' => 'payment.order.gateway.form',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Order\\Admin\\SearchOrderForm',
        'type' => 'form',
        'name' => 'payment.order.search_form',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Transaction\\Admin\\SearchTransactionForm',
        'type' => 'form',
        'name' => 'payment.transaction.search_form',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Form\\Html\\GatewayButton',
        'type' => 'form-field',
        'name' => 'gatewayButton',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form Field'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type' => 'form-settings',
        'name' => 'payment',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'core::phrase.settings',
        'url' => '/payment/setting',
        'type_label' => 'Form Settings'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\GatewayEmbedCollection',
        'type' => 'json-collection',
        'name' => 'payment.gateway.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\GatewayItemCollection',
        'type' => 'json-collection',
        'name' => 'payment.gateway.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\GatewayDetail',
        'type' => 'json-resource',
        'name' => 'payment.gateway.detail',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\GatewayEmbed',
        'type' => 'json-resource',
        'name' => 'payment.gateway.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\GatewayItem',
        'type' => 'json-resource',
        'name' => 'payment.gateway.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\PackageSetting',
        'type' => 'package-setting',
        'name' => 'payment',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'MetaFox\\Payment\\Policies\\GatewayPolicy',
        'type' => 'policy-resource',
        'name' => 'MetaFox\\Payment\\Models\\Gateway',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Policy'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Order\\Admin\\WebSetting',
        'type' => 'resource-web',
        'name' => 'order',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\Admin\\WebSetting',
        'type' => 'resource-web',
        'name' => 'payment_gateway',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Transaction\\Admin\\WebSetting',
        'type' => 'resource-web',
        'name' => 'payment_transaction',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web',
        'alias' => 'transaction'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\Gateway\\GatewayEmbed',
        'type' => 'json-resource',
        'name' => 'gateway.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\PaymentRequest\\PasswordVerificationForm',
        'type' => 'form',
        'name' => 'payment.payment_request.password_verification',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'MetaFox\\Payment\\Http\\Resources\\v1\\PaymentRequest\\PasswordVerificationMobileForm',
        'type' => 'form',
        'name' => 'payment.payment_request.password_verification',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
];
