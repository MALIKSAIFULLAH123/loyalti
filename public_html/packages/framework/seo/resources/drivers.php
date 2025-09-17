<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\SEO\\Http\\Resources\\v1\\Meta\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'seo.meta',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver' => 'MetaFox\\SEO\\Models\\Meta',
        'type'   => 'entity',
        'name'   => 'meta',
    ],
    [
        'driver'     => 'MetaFox\\SEO\\Http\\Resources\\v1\\Meta\\Admin\\DestroyMetaForm',
        'type'       => 'form',
        'name'       => 'seo.meta.destroy',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\SEO\\Http\\Resources\\v1\\Meta\\Admin\\StoreMetaForm',
        'type'       => 'form',
        'name'       => 'seo.meta.store',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\SEO\\Http\\Resources\\v1\\Meta\\Admin\\UpdateMetaForm',
        'type'       => 'form',
        'name'       => 'seo.meta.update',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\SEO\\Http\\Resources\\v1\\Meta\\Admin\\UpdateSchemaMetaForm',
        'type'       => 'form',
        'name'       => 'seo.meta.update_schema',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'      => 'MetaFox\\SEO\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'        => 'form-settings',
        'name'        => 'seo',
        'version'     => 'v1',
        'resolution'  => 'admin',
        'title'       => 'core::phrase.settings',
        'url'         => '/seo/setting',
        'description' => 'seo::phrase.site_settings_desc',
    ],
    [
        'driver'  => 'MetaFox\\SEO\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'seo',
        'version' => 'v1',
    ],
];
