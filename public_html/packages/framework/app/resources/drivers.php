<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'app.package.installed',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\UploadedDataGrid',
        'type'       => 'data-grid',
        'name'       => 'app.package.uploaded',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\PurchasedDataGrid',
        'type'       => 'data-grid',
        'name'       => 'app.package.purchased',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver' => 'MetaFox\\App\\Models\\Package',
        'type'   => 'entity',
        'name'   => 'package',
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\ImportPackageForm',
        'type'       => 'form',
        'name'       => 'core.package.import',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\SearchPackageForm',
        'type'       => 'form',
        'name'       => 'core.package.search',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\EditPackageForm',
        'type'       => 'form',
        'name'       => 'core.package.update',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'  => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\PackageDetail',
        'type'    => 'json-resource',
        'name'    => 'package.detail',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\PackageItem',
        'type'    => 'json-resource',
        'name'    => 'package.item',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\App\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'metafox/app',
        'version' => 'v1',
    ],
    [
        'driver'     => 'MetaFox\\App\\Policies\\PackagePolicy',
        'type'       => 'policy-resource',
        'name'       => 'MetaFox\\App\\Models\\Package',
        'is_deleted' => 1,
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\AppStoreProduct\\Admin\\WebSetting',
        'type'       => 'resource-web',
        'name'       => 'app_store_product',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\App\\Http\\Resources\\v1\\Package\\Admin\\WebSetting',
        'type'       => 'resource-web',
        'name'       => 'package',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
];
