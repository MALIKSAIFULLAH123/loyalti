<?php

namespace MetaFox\Activity\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Http\Resources\FeedParamItemTrait;

trait RelatedCommentsTrait
{
    use FeedParamItemTrait;

    public function relatedComments(User $context, ?Entity $content = null, array $extra = []): JsonResource
    {
        if (!$content instanceof HasTotalComment) {
            return new JsonResource([]);
        }

        /** @var JsonResource|mixed $response */
        $response = app('events')->dispatch('comment.related_comments', [$context, $content, $extra], true);

        if (!$response instanceof JsonResource) {
            return new JsonResource([]);
        }

        return $response;
    }
}
