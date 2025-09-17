<?php

namespace MetaFox\Comment\Listeners;

use Illuminate\Http\Resources\Json\ResourceCollection;
use MetaFox\Comment\Http\Resources\v1\Comment\CommentItemCollection;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;

class RelevantCommentByIdListener
{
    public function handle(?User $context, int $id, ?Entity $content = null, bool $isFeed = true): ?ResourceCollection
    {
        if (!$context) {
            return null;
        }

        if (!$content instanceof HasTotalComment) {
            return null;
        }

        $collection = resolve(CommentRepositoryInterface::class)->getRelevantCommentsById($context, $id, $content, $isFeed);

        if (null === $collection) {
            return null;
        }

        return new CommentItemCollection($collection);
    }
}
