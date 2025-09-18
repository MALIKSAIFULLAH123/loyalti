<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'quiz',
        'label'       => 'quiz::phrase.quiz',
        'ordering'    => 22,
        'to'          => '/quiz/quiz/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'quiz.admin',
        'name'     => 'settings',
        'label'    => 'quiz::phrase.settings',
        'ordering' => 0,
        'to'       => '/quiz/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'quiz.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 1,
        'to'       => '/quiz/permission',
    ],
    [
        'menu'     => 'quiz.admin',
        'name'     => 'manage_quizzes',
        'label'    => 'quiz::phrase.manage_quizzes',
        'ordering' => 3,
        'to'       => '/quiz/quiz/browse',
    ],
];
