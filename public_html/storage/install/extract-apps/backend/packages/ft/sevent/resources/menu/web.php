<?php
/* this is auto generated file */
return [
    [
        'subInfo' => 'sevent::phrase.browse_sevents_you_like_to_read',
        'is_custom' => false,
        'menu' => 'core.dropdownMenu',
        'name' => 'sevents',
        'label' => 'Advanced Events',
        'ordering' => 3,
        'icon' => 'ico-calendar',
        'to' => '/sevent'
    ],
    [
        'subInfo' => 'sevent::phrase.browse_sevents_you_like_to_read',
        'is_custom' => false,
        'menu' => 'core.primaryMenu',
        'name' => 'sevents',
        'label' => 'Advanced Events',
        'ordering' => 2,
        'icon' => 'ico-calendar',
        'to' => '/sevent'
    ],
    [
        'tab' => 'sevent',
        'showWhen' => [
            'or',
            ['eq', 'item.reg_method', '0'],
            [
                'or',
                ['truthy', 'acl.group.group.moderate'],
                ['truthy', 'item.is_member']
            ]
        ],
        'is_custom' => false,
        'menu' => 'group.group.profileMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 5,
        'to' => '/sevent'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.sevent.sevent.view']
        ],
        'is_custom' => false,
        'menu' => 'group.searchWebCategoryMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 2
    ],
    [
        'tab' => 'sevent',
        'showWhen' => [],
        'is_custom' => false,
        'menu' => 'page.page.profileMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 9,
        'to' => '/sevent'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.sevent.sevent.view']
        ],
        'is_custom' => false,
        'menu' => 'search.webCategoryMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 3,
        'to' => '/search/sevent'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.sevent.sevent.view']
        ],
        'is_custom' => false,
        'menu' => 'search.webCategoryOrderingMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 2
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.sevent.sevent.view']
        ],
        'is_custom' => false,
        'menu' => 'search.webHashtagCategoryMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 2
    ],
    [
        'params' => [
            'interestedEvent' => [
                'removeEntity' => true
            ]
        ],
        'showWhen' => [
            'and',
            ['falsy', 'item.is_pending']
        ],
        'is_custom' => false,
        'menu' => 'sevent.attendMenu',
        'name' => 'interested',
        'label' => 'sevent::phrase.sevent_interested',
        'ordering' => 1,
        'value' => 'interestedEvent',
        'icon' => 'ico-calendar-star'
    ],
    [
        'color' => 'primary',
        'params' => [
            'joinEvent' => [
                'removeEntity' => true
            ]
        ],
        'showWhen' => [
            'and',
            ['falsy', 'item.is_pending']
        ],
        'is_custom' => false,
        'menu' => 'sevent.attendMenu',
        'name' => 'going',
        'label' => 'sevent::phrase.sevent_going',
        'ordering' => 2,
        'value' => 'joinEvent',
        'icon' => 'ico-user3-check-o'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent_ticket.itemActionMenu',
        'name' => 'edit',
        'label' => 'Edit Ticket',
        'ordering' => 1,
        'value' => 'editItem',
        'icon' => 'ico-pencilline-o'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete']
        ],
        'className' => 'itemDelete',
        'is_custom' => false,
        'menu' => 'sevent.sevent_ticket.itemActionMenu',
        'name' => 'delete',
        'label' => 'Delete',
        'ordering' => 13,
        'value' => 'deleteItem',
        'icon' => 'ico-trash'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'edit',
        'label' => 'Edit Event',
        'ordering' => 1,
        'value' => 'editItem',
        'icon' => 'ico-pencilline-o'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'ticket',
        'label' => 'Add Ticket',
        'ordering' => 2,
        'value' => 'sevent/addTicketItem',
        'icon' => 'ico-plus',
        'to' => ''
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_publish'],
            ['truthy', 'item.is_draft']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'publish',
        'label' => 'Publish',
        'ordering' => 3,
        'value' => 'publishSevent',
        'icon' => 'ico-check'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit'],
            ['falsy', 'item.is_pending']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'mass_email_guest',
        'label' => 'Mass Email Guest',
        'ordering' => 3,
        'value' => 'sevent/massEmailEvent',
        'icon' => 'ico-envelope-o'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'sponsor_in_feed',
        'label' => 'Sponsor in Feed',
        'ordering' => 5,
        'value' => 'sponsorItemInFeed',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_purchase_sponsor_in_feed']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'purchase_sponsor_in_feed',
        'label' => 'Sponsor in Feed',
        'ordering' => 5,
        'value' => 'advertise/purchaseSponsorItemInFeed',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unsponsor_in_feed']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'remove_sponsor_in_feed',
        'label' => 'Unsponsor in Feed',
        'ordering' => 6,
        'value' => 'unsponsorItemInFeed',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_sponsor']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'sponsor',
        'label' => 'Sponsor this item',
        'ordering' => 7,
        'value' => 'sponsorItem',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_purchase_sponsor']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'purchase_sponsor',
        'label' => 'Sponsor this item',
        'ordering' => 7,
        'value' => 'advertise/purchaseSponsorItem',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unsponsor']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'remove_sponsor',
        'label' => 'Unsponsor this item',
        'ordering' => 8,
        'value' => 'unsponsorItem',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['falsy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature'],
            ['falsy', 'item.is_draft']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'feature',
        'label' => 'Feature',
        'ordering' => 9,
        'value' => 'featureItem',
        'icon' => 'ico-diamond'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature'],
            ['falsy', 'item.is_draft']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'remove_feature',
        'label' => 'Unfeature',
        'ordering' => 10,
        'value' => 'unfeatureItem',
        'icon' => 'ico-diamond'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete']
        ],
        'className' => 'itemDelete',
        'is_custom' => false,
        'menu' => 'sevent.sevent.detailActionMenu',
        'name' => 'delete',
        'label' => 'Delete',
        'ordering' => 13,
        'value' => 'deleteItem',
        'icon' => 'ico-trash'
    ],
    [
        'tab' => 'sevent',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
            ['truthy', 'acl.sevent.sevent.create'],
            ['truthy', 'item.profile_settings.sevent_share_sevents'],
            ['falsy', 'item.is_muted'],
            ['falsy', 'item.is_pending']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.headerItemActionOnGroupProfileMenu',
        'name' => 'sevent',
        'label' => 'Add New Event',
        'ordering' => 1,
        'to' => '/sevent/add?owner_id=:id'
    ],
    [
        'tab' => 'sevent',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
            ['truthy', 'acl.sevent.sevent.create'],
            ['truthy', 'item.profile_settings.sevent_share_sevents'],
            ['falsy', 'item.is_pending']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.headerItemActionOnPageProfileMenu',
        'name' => 'sevent',
        'label' => 'Add New Event',
        'ordering' => 1,
        'to' => '/sevent/add?owner_id=:id'
    ],
    [
        'tab' => 'sevent',
        'showWhen' => [
            'and',
            ['truthy', 'acl.sevent.sevent.create'],
            ['truthy', 'item.is_owner']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.headerItemActionOnUserProfileMenu',
        'name' => 'sevent',
        'label' => 'Add New Event',
        'ordering' => 1,
        'to' => '/sevent/add?owner_id=:id'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'edit',
        'label' => 'Edit Event',
        'ordering' => 1,
        'value' => 'editItem',
        'icon' => 'ico-pencilline-o'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_publish'],
            ['truthy', 'item.is_draft']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'publish',
        'label' => 'Publish',
        'ordering' => 3,
        'value' => 'publishSevent',
        'icon' => 'ico-check'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.is_pending'],
            ['truthy', 'item.extra.can_approve']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'approve',
        'label' => 'Approve',
        'ordering' => 4,
        'value' => 'approveItem',
        'icon' => 'ico-check-circle-o'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_sponsor_in_feed']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'sponsor_in_feed',
        'label' => 'Sponsor in Feed',
        'ordering' => 5,
        'value' => 'sponsorItemInFeed',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_purchase_sponsor_in_feed']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'purchase_sponsor_in_feed',
        'label' => 'Sponsor in Feed',
        'ordering' => 5,
        'value' => 'advertise/purchaseSponsorItemInFeed',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unsponsor_in_feed']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'remove_sponsor_in_feed',
        'label' => 'Unsponsor in Feed',
        'ordering' => 6,
        'value' => 'unsponsorItemInFeed',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_sponsor']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'sponsor',
        'label' => 'Sponsor this item',
        'ordering' => 7,
        'value' => 'sponsorItem',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_purchase_sponsor']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'purchase_sponsor',
        'label' => 'Sponsor this item',
        'ordering' => 7,
        'value' => 'advertise/purchaseSponsorItem',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unsponsor']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'remove_sponsor',
        'label' => 'Unsponsor this item',
        'ordering' => 8,
        'value' => 'unsponsorItem',
        'icon' => 'ico-sponsor'
    ],
    [
        'showWhen' => [
            'and',
            ['falsy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature'],
            ['falsy', 'item.is_draft']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'feature',
        'label' => 'Feature',
        'ordering' => 9,
        'value' => 'featureItem',
        'icon' => 'ico-diamond'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.is_featured'],
            ['truthy', 'item.extra.can_feature'],
            ['falsy', 'item.is_draft']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'remove_feature',
        'label' => 'Unfeature',
        'ordering' => 10,
        'value' => 'unfeatureItem',
        'icon' => 'ico-diamond'
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete']
        ],
        'className' => 'itemDelete',
        'is_custom' => false,
        'menu' => 'sevent.sevent.itemActionMenu',
        'name' => 'delete',
        'label' => 'Delete',
        'ordering' => 13,
        'value' => 'deleteItem',
        'icon' => 'ico-trash'
    ],
    [
        'tab' => 'landing',
        'is_custom' => false,
        'menu' => 'sevent.sidebarMenu',
        'name' => 'landing',
        'label' => 'Home',
        'ordering' => 1,
        'icon' => 'ico-calendar',
        'to' => '/sevent'
    ],
    [
        'tab' => 'all',
        'is_custom' => false,
        'menu' => 'sevent.sidebarMenu',
        'name' => 'browse',
        'label' => 'Browse Events',
        'ordering' => 2,
        'icon' => 'ico-list-bullet-o',
        'to' => '/sevent/all'
    ],
    [
        'tab' => 'attending',
        'is_custom' => false,
        'menu' => 'sevent.sidebarMenu',
        'name' => 'attending',
        'label' => 'Attending',
        'ordering' => 3,
        'icon' => 'ico-calendar-check-o',
        'to' => '/sevent/attending'
    ],
    [
        'tab' => 'sevent_user_ticket',
        'is_custom' => false,
        'menu' => 'sevent.sidebarMenu',
        'name' => 'sevent_user_ticket',
        'label' => 'My Tickets',
        'ordering' => 4,
        'icon' => 'ico-ticket-o',
        'to' => '/sevent/ticket/my'
    ],
    [
        'tab' => 'on_map',
        'is_custom' => false,
        'menu' => 'sevent.sidebarMenu',
        'name' => 'on_map',
        'label' => 'View on Map',
        'ordering' => 4,
        'icon' => 'ico-map-o',
        'to' => '/sevent/search-map?view=on_map'
    ],
    [
        'tab' => 'my',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'my',
        'label' => 'My Events',
        'ordering' => 1,
        'icon' => 'ico-user-man-o',
        'to' => '/sevent/my'
    ],
    [
        'tab' => 'favourite_sevents',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'favourite',
        'label' => 'My Diary',
        'ordering' => 2,
        'icon' => 'ico-star-o',
        'to' => '/sevent/favourite_sevents'
    ],
    [
        'tab' => 'bought',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'bought',
        'label' => 'Invoice Bought',
        'ordering' => 4,
        'icon' => 'ico-merge-file-o',
        'to' => '/sevent/invoice-bought'
    ],
    [
        'tab' => 'sold',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'sold',
        'label' => 'Invoice Sold',
        'ordering' => 5,
        'icon' => 'ico-merge-file-o',
        'to' => '/sevent/invoice-sold'
    ],
    [
        'tab' => 'my_pending',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
            ['neq', 'session.user.role.id', 1]
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'my_pending',
        'label' => 'My Pending Events',
        'ordering' => 6,
        'icon' => 'ico-user1-clock-o',
        'to' => '/sevent/my-pending'
    ],
    [
        'tab' => 'draft',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'draft',
        'label' => 'My Draft Events',
        'ordering' => 8,
        'icon' => 'ico-pencilline-o',
        'to' => '/sevent/draft'
    ],
    [
        'tab' => 'pending',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
            ['truthy', 'acl.sevent.sevent.approve']
        ],
        'is_custom' => false,
        'menu' => 'sevent.sidebarMyMenu',
        'name' => 'pending',
        'label' => 'Pending Events',
        'ordering' => 9,
        'icon' => 'ico-clock-o',
        'to' => '/sevent/pending'
    ],
    [
        'tab' => 'sevent',
        'showWhen' => [
            'and',
            ['truthy', 'item.profile_menu_settings.sevent']
        ],
        'is_custom' => false,
        'menu' => 'user.user.profileMenu',
        'name' => 'sevent',
        'label' => 'Events',
        'ordering' => 9,
        'to' => '/sevent'
    ]
];
