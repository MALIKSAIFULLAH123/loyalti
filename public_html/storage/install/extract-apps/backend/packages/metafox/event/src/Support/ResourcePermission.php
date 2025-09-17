<?php

namespace MetaFox\Event\Support;

use MetaFox\Platform\ResourcePermission as PlatformResourcePermission;

class ResourcePermission extends PlatformResourcePermission
{
    public const CAN_VIEW_DISCUSSION     = 'can_view_discussion';
    public const CAN_VIEW_HOSTS          = 'can_view_hosts';
    public const CAN_VIEW_MEMBERS        = 'can_view_members';
    public const CAN_CREATE_DISCUSSION   = 'can_create_discussion';
    public const CAN_MANAGE_PENDING_POST = 'can_manage_pending_post';
    public const CAN_INVITE              = 'can_invite';
    public const CAN_MANAGE_HOST         = 'can_manage_host';
    public const CAN_RSVP                = 'can_rsvp';
    public const CAN_MASS_EMAIL          = 'can_mass_email';
    public const CAN_MASS_INVITE         = 'can_mass_invite';

    public const CAN_REMOVE_HOST     = 'can_remove_host';
    public const CAN_REMOVE_INVITE   = 'can_remove_invite';
    public const CAN_DUPLICATE_EVENT = 'can_duplicate_event';
    public const CAN_EXPORT          = 'can_export';
}
