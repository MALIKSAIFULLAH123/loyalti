<?php

namespace MetaFox\Photo\Support;

class ResourcePermission
{
    public const CAN_SET_PROFILE_AVATAR   = 'can_set_profile_avatar';
    public const CAN_SET_PROFILE_COVER    = 'can_set_profile_cover';
    public const CAN_SET_PARENT_COVER     = 'can_set_parent_cover';
    public const CAN_SET_PARENT_AVATAR    = 'can_set_parent_avatar';
    public const CAN_TAG_FRIEND           = 'can_tag_friend';
    public const CAN_REMOVE_TAGGED_FRIEND = 'can_remove_tag_friend';
    public const CAN_VIEW_MATURE          = 'can_view_mature';
    public const CAN_EDIT_MATURE          = 'can_edit_mature';
}
