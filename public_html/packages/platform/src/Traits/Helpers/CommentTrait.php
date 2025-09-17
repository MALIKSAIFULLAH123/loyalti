<?php

namespace MetaFox\Platform\Traits\Helpers;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Core\Models\ItemStatistics;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Trait CommentStatisticTrait.
 * @property ItemStatistics|null $item_statistic
 * @mixin HasTotalComment
 */
trait CommentTrait
{
    public function comments(): MorphMany
    {
        /** @var string $related */
        $related = Relation::getMorphedModel('comment');

        return $this->morphMany($related, 'item', 'item_type', 'item_id', $this->primaryKey);
    }

    public function getTotalCommentAttribute(): int
    {
        $totalComment = Arr::get($this->attributes, 'total_comment', 0);
        $totalComment = max($totalComment, 0);
        if (!$this->total_pending_comment) {
            return $totalComment;
        }

        if (Auth::guest()) {
            return $totalComment;
        }

        $context = user();

        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return $totalComment + max($this->total_pending_comment, 0);
        }

        $commentModel = Relation::getMorphedModel('comment');
        if (!$commentModel) {
            return 0;
        }

        $pendingComment = LoadReduce::get(
            sprintf(
                'comment::totalPendingComment(user:%s,%s:%s)',
                $context->entityId(),
                $this->entityType(),
                $this->entityId()
            ),
            fn() => $this->total_pending_user_comment
        );

        return $totalComment + max($pendingComment, 0);
    }

    public function getTotalReplyAttribute(): int
    {
        $totalReply = Arr::get($this->attributes, 'total_reply', 0);
        $totalReply = max($totalReply, 0);

        if (!$this->total_pending_reply) {
            return $totalReply;
        }

        if (Auth::guest()) {
            return 0;
        }

        $context = user();

        if ($context->hasPermissionTo('admincp.has_admin_access')) {
            return max($this->total_pending_reply, 0) + $totalReply;
        }

        /** @link \MetaFox\Comment\Support\LoadMissingTotalPendingReply::handle */
        $replies = LoadReduce::get(
            sprintf('comment::totalPendingReply(user:%s,%s:%s)', $context->id, $this->entityType(), $this->entityId()),
            fn() => $this->total_pending_user_reply
        );

        return $totalReply + $replies;
    }

    public function getTotalPendingUserCommentAttribute(): int
    {
        if (!$this->total_pending_comment) {
            return 0;
        }

        $userId = Auth::user()?->entityId();

        if (!$userId) {
            return 0;
        }

        $conditions = [
            'parent_id'   => 0,
            'user_id'     => $userId,
            'is_approved' => 0,
            'item_id'     => $this->entityId(),
            'item_type'   => $this->entityType(),
        ];

        $commentModel = Relation::getMorphedModel('comment');
        if (!$commentModel) {
            return 0;
        }

        if ($this instanceof $commentModel) {
            $conditions = array_merge($conditions, [
                'parent_id' => $this->entityId(),
                'item_id'   => $this->item->entityId(),
                'item_type' => $this->item->entityType(),
            ]);
        }

        return $commentModel::query()->where($conditions)->count();
    }

    public function getTotalPendingUserReplyAttribute(): int
    {
        if (!$this->total_pending_reply) {
            return 0;
        }

        $userId = Auth::user()?->entityId();

        if (!$userId) {
            return 0;
        }

        $conditions = [
            'item_id'     => $this->entityId(),
            'item_type'   => $this->entityType(),
            'user_id'     => $userId,
            'is_approved' => 0,
        ];

        $commentModel = Relation::getMorphedModel('comment');

        if (!$commentModel) {
            return 0;
        }

        return $commentModel::query()->where($conditions)->count();
    }

    public function getItemStatistic(): ?ItemStatistics
    {
        return $this->item_statistic;
    }

    public function itemStatistic(): ?MorphOne
    {
        return $this->morphOne(ItemStatistics::class, 'itemStatistic', 'item_type', 'item_id');
    }

    public function getItemStatisticAttribute()
    {
        return LoadReduce::remember(
            sprintf('statistic::(%s,%s)', $this->entityType(), $this->entityId()),
            fn() => $this->getRelationValue('itemStatistic')
        );
    }
}
