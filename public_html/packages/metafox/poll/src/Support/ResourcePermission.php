<?php

namespace MetaFox\Poll\Support;

class ResourcePermission
{
    public const CAN_LIKE                    = 'can_like';
    public const CAN_SHARE                   = 'can_share';
    public const CAN_DELETE_OWN              = 'can_delete_own';
    public const CAN_COMMENT                 = 'can_comment';
    public const CAN_FEATURE                 = 'can_feature';
    public const CAN_VIEW_HIDE_VOTE          = 'can_view_hide_vote';
    public const CAN_VOTE_WITH_CLOSE_TIME    = 'can_vote_with_close_time';
    public const CAN_VOTE                    = 'can_vote';
    public const CAN_CHANGE_VOTE             = 'can_change_vote';
    public const CAN_APPROVE                 = 'can_approve';
    public const CAN_VIEW_RESULT             = 'can_view_result';
    public const CAN_VIEW_RESULT_BEFORE_VOTE = 'can_view_result_before_vote';
    public const CAN_VIEW_RESULT_AFTER_VOTE  = 'can_view_result_after_vote';
}
