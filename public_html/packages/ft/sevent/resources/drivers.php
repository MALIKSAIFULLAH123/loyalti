<?php
/* this is auto generated file */
return [
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\Admin\\DataGrid',
        'type' => 'data-grid',
        'name' => 'sevent.category',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Data Grid'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\Admin\\DataGrid',
        'type' => 'data-grid',
        'name' => 'sevent.category.category',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Data Grid'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Sevent',
        'type' => 'entity',
        'name' => 'sevent',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevents',
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Attend',
        'type' => 'entity',
        'name' => 'sevent_attends',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent Attending',
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Category',
        'type' => 'entity',
        'name' => 'sevent_category',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent Categories',
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\CategoryData',
        'type' => 'entity',
        'name' => 'sevent_category_data',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent Category Data',
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Invoice',
        'type' => 'entity',
        'name' => 'sevent_invoice',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\InvoiceTransaction',
        'type' => 'entity',
        'name' => 'sevent_invoice_transaction',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Ticket',
        'type' => 'entity',
        'name' => 'sevent_ticket',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent Ticket',
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\UserTicket',
        'type' => 'entity',
        'name' => 'sevent_user_ticket',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent User Ticket',
        'type_label' => 'Entity'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Sevent',
        'type' => 'entity-content',
        'name' => 'sevent',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevents'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Attend',
        'type' => 'entity-content',
        'name' => 'sevent_attend',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent Attending'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\Ticket',
        'type' => 'entity-content',
        'name' => 'sevent_ticket',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent Tickets'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Models\\UserTicket',
        'type' => 'entity-content',
        'name' => 'sevent_user_ticket',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'Sevent User Tickets'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Invoice\\SearchBoughtInvoiceForm',
        'type' => 'form',
        'name' => 'sevent_invoice.bought_search',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Invoice\\SearchSoldInvoiceForm',
        'type' => 'form',
        'name' => 'sevent_invoice.sold_search',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Ticket\\StoreTicketForm',
        'type' => 'form',
        'name' => 'sevent_ticket.store',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Ticket\\UpdateTicketForm',
        'type' => 'form',
        'name' => 'sevent_ticket.update',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\MassEmailForm',
        'type' => 'form',
        'name' => 'sevent.mass_email',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\PaymentSeventForm',
        'type' => 'form',
        'name' => 'sevent.payment',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SearchSeventForm',
        'type' => 'form',
        'name' => 'sevent.search',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SearchSeventMobileForm',
        'type' => 'form',
        'name' => 'sevent.search',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SearchInOwnerMobileForm',
        'type' => 'form',
        'name' => 'sevent.search_in_owner',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SearchSeventMapForm',
        'type' => 'form',
        'name' => 'sevent.search_map',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => true,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\Admin\\DestroyCategoryForm',
        'type' => 'form',
        'name' => 'sevent.sevent_category.destroy',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\Admin\\StoreCategoryForm',
        'type' => 'form',
        'name' => 'sevent.sevent_category.store',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\Admin\\UpdateCategoryForm',
        'type' => 'form',
        'name' => 'sevent.sevent_category.update',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\StoreSeventMobileForm',
        'type' => 'form',
        'name' => 'sevent.sevent.store',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\UpdateSeventMobileForm',
        'type' => 'form',
        'name' => 'sevent.sevent.update',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\StoreSeventForm',
        'type' => 'form',
        'name' => 'sevent.store',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\StoreSeventMobileForm',
        'type' => 'form',
        'name' => 'sevent.store_sevent_mobile',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\UpdateSeventForm',
        'type' => 'form',
        'name' => 'sevent.update',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Form'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type' => 'form-settings',
        'name' => 'sevent',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'title' => 'core::phrase.settings',
        'url' => '/sevent/setting',
        'description' => 'sevent::phrase.edit_sevent_setting_desc',
        'type_label' => 'Form Settings'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Jobs\\DeleteCategoryJob',
        'type' => 'job',
        'name' => 'Foxexpert\\Sevent\\Jobs\\DeleteCategoryJob',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Job'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\CategoryEmbedCollection',
        'type' => 'json-collection',
        'name' => 'sevent_category.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\CategoryItemCollection',
        'type' => 'json-collection',
        'name' => 'sevent_category.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\InvoiceTransaction\\TransactionItemCollection',
        'type' => 'json-collection',
        'name' => 'sevent_invoice_transaction.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Ticket\\TicketItemCollection',
        'type' => 'json-collection',
        'name' => 'sevent_ticket.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SeventEmbedCollection',
        'type' => 'json-collection',
        'name' => 'sevent.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SeventItemCollection',
        'type' => 'json-collection',
        'name' => 'sevent.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\CategoryDetail',
        'type' => 'json-resource',
        'name' => 'sevent_category.detail',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\CategoryEmbed',
        'type' => 'json-resource',
        'name' => 'sevent_category.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\CategoryItem',
        'type' => 'json-resource',
        'name' => 'sevent_category.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Image\\ImageItem',
        'type' => 'json-resource',
        'name' => 'sevent_image.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\InvoiceTransaction\\TransactionItem',
        'type' => 'json-resource',
        'name' => 'sevent_invoice_transaction.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Ticket\\TicketItem',
        'type' => 'json-resource',
        'name' => 'sevent_ticket.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SeventDetail',
        'type' => 'json-resource',
        'name' => 'sevent.detail',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SeventEmbed',
        'type' => 'json-resource',
        'name' => 'sevent.embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\FeedEmbed',
        'type' => 'json-resource',
        'name' => 'sevent.feed_embed',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\SeventItem',
        'type' => 'json-resource',
        'name' => 'sevent.item',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Json Resource'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\PackageSetting',
        'type' => 'package-setting',
        'name' => 'sevent',
        'version' => 'v1',
        'is_active' => true,
        'is_preload' => false
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Policies\\CategoryPolicy',
        'type' => 'policy-resource',
        'name' => 'Foxexpert\\Sevent\\Models\\Category',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Policy'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Policies\\InvoicePolicy',
        'type' => 'policy-resource',
        'name' => 'Foxexpert\\Sevent\\Models\\Invoice',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Policy'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Policies\\SeventPolicy',
        'type' => 'policy-resource',
        'name' => 'Foxexpert\\Sevent\\Models\\Sevent',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Policy'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\MobileSetting',
        'type' => 'resource-mobile',
        'name' => 'sevent',
        'version' => 'v1',
        'resolution' => 'mobile',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Mobile'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Sevent\\WebSetting',
        'type' => 'resource-web',
        'name' => 'sevent',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Category\\Admin\\WebSetting',
        'type' => 'resource-web',
        'name' => 'sevent_category',
        'version' => 'v1',
        'resolution' => 'admin',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Invoice\\WebSetting',
        'type' => 'resource-web',
        'name' => 'sevent_invoice',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web'
    ],
    [
        'driver' => 'Foxexpert\\Sevent\\Http\\Resources\\v1\\Ticket\\WebSetting',
        'type' => 'resource-web',
        'name' => 'sevent_ticket',
        'version' => 'v1',
        'resolution' => 'web',
        'is_active' => true,
        'is_preload' => false,
        'type_label' => 'Resource Web'
    ]
];
