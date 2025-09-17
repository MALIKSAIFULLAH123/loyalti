<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasTotalComment.
 * @property int $total_comment              Total approved comment
 * @property int $total_pending_comment
 * @property int $total_pending_reply
 * @property int $total_reply                Total approved comment
 * @property int $total_pending_user_reply   Total pending reply of current user
 * @property int $total_pending_user_comment Total pending comment of current user
 */
interface HasTotalComment extends Entity, HasAmounts
{
    public const TOTAL_PENDING_COMMENT = 'total_pending_comment';

    public const TOTAL_PENDING_REPLY = 'total_pending_reply';
    public const TOTAL_COMMENT       = 'total_comment';
    public const TOTAL_REPLY         = 'total_reply';
}
