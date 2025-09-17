<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\Rewrite\\Http\\Resources\\v1\\Rule\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'rewrite.rule',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'rewrite::phrase.browse_rules',
        'url'        => '/rewrite/rule/browse',
    ],
    [
        'driver' => 'MetaFox\\Rewrite\\Models\\Rule',
        'type'   => 'entity',
        'name'   => 'rewrite_rule',
    ],
    [
        'driver'     => 'MetaFox\\Rewrite\\Http\\Resources\\v1\\Rule\\Admin\\StoreRuleForm',
        'type'       => 'form',
        'name'       => 'rewrite.rule.store',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\Rewrite\\Http\\Resources\\v1\\Rule\\Admin\\UpdateRuleForm',
        'type'       => 'form',
        'name'       => 'rewrite.rule.update',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'  => 'MetaFox\\Rewrite\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'rewrite',
        'version' => 'v1',
    ],
];
