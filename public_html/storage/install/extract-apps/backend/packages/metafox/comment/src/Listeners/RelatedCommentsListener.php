<?php

namespace MetaFox\Comment\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Comment\Http\Resources\v1\Comment\CommentItemCollection;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

class RelatedCommentsListener
{
    /**
     * @param  User|null    $context
     * @param  mixed        $content
     * @param  array        $extra
     * @return JsonResource
     */
    public function handle(?User $context, mixed $content, array $extra = []): JsonResource
    {
        if (!$content instanceof HasTotalComment || !$context) {
            return new JsonResource([]);
        }

        if (!policy_check(CommentPolicy::class, 'viewAny', $context, $content?->owner)) {
            return new JsonResource([]);
        }

        /** @link \MetaFox\Comment\Support\LoadMissingRelatedComments::before */
        /** @var $comments */
        $comments = LoadReduce::get(
            sprintf('comment::relatedCommentsByType(user:%s,%s:%s)', $context->id, $content->entityType(), $content->entityId()),
            fn () => resolve(CommentRepositoryInterface::class)
                ->getRelatedCommentsByType($context, $content->entityType(), $content->entityId(), $extra)
        );

        return new CommentItemCollection($comments);
    }
}
