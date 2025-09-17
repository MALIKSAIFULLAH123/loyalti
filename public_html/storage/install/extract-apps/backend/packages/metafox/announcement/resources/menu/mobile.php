<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_close'],
        ],
        'menu'     => 'announcement.announcement.itemActionMenu',
        'name'     => 'close',
        'label'    => 'announcement::phrase.close_announcement',
        'ordering' => 1,
        'value'    => 'announcement/closeAnnouncement',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_close'],
        ],
        'menu'     => 'announcement.announcement.detailActionMenu',
        'name'     => 'close',
        'label'    => 'announcement::phrase.close_announcement',
        'ordering' => 1,
        'value'    => 'announcement/closeAnnouncement',
    ],
];
