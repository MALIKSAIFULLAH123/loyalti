<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use MetaFox\Comment\Models\Comment as Model;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;

/**
 * Class CommentItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CommentListingItem extends CommentItem
{
    protected function getChildrens(): ?ResourceCollection
    {
        return null;
    }

    public function toArray($request): array
    {
        $data = parent::toArray($request);

        /**
         * For mobile of viewing detail comment/reply.
         */
        $commentId = request()->get('parent_comment_id');

        $childCommentId = request()->get('child_comment_id');

        if (!$childCommentId) {
            return $data;
        }

        if ($commentId != $this->resource->entityId()) {
            return $data;
        }

        $reply = resolve(CommentRepositoryInterface::class)->getModel()->newQuery()
            ->where('id', '=', $childCommentId)
            ->first();

        if (null === $reply) {
            return $data;
        }

        $collection = new CommentItemCollection([$reply]);

        Arr::set($data, 'default_children', $collection);

        return $data;
    }
}
