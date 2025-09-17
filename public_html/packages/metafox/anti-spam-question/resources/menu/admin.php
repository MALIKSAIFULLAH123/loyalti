<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'settings',
        'name'        => 'antispamquestion',
        'label'       => 'antispamquestion::phrase.app_name',
        'ordering'    => 1,
        'to'          => '/antispamquestion/question/browse',
    ],
    [
        'menu'  => 'antispamquestion.admin',
        'name'  => 'settings',
        'label' => 'core::phrase.settings',
        'to'    => '/antispamquestion/setting',
    ],
    [
        'menu'  => 'antispamquestion.admin',
        'name'  => 'manage_question',
        'label' => 'antispamquestion::phrase.manage_questions',
        'to'    => '/antispamquestion/question/browse',
    ],
];
