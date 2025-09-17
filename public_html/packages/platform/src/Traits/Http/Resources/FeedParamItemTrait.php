<?php

namespace MetaFox\Platform\Traits\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;

trait FeedParamItemTrait
{
    public function relatedCommentsItemDetail(User $context, ?Entity $content = null, int $limit = 6): JsonResource
    {
        if (!$content instanceof HasTotalComment) {
            return new JsonResource([]);
        }

        /** @var JsonResource|mixed $response */
        $response = app('events')->dispatch(
            'comment.related_comments.item_detail',
            [$context, $content, $limit],
            true
        );

        if (!$response instanceof JsonResource) {
            return new JsonResource([]);
        }

        return $response;
    }
}
