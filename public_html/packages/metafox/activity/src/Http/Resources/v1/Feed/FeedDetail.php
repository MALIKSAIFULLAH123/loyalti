<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\Feed as Model;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class FeedDetail.
 *
 * @property Model $resource
 */
class FeedDetail extends FeedItem
{
    public function toArray($request): array
    {
        $response = parent::toArray($request);

        $commentId = $request->get('comment_id');

        if ($commentId) {
            $resource = $this->getActionResource();

            $relevantComment = app('events')->dispatch('comment.relevant_comment_by_id', [user(), $commentId, $resource], true);

            if (null !== $relevantComment) {
                Arr::set($response, 'relevant_comments', $relevantComment);
            }
        }

        return $response;
    }

    protected function isSponsored(Request $request): bool
    {
        return $this->resource->isSponsored() || $this->resource->isSponsoredInFeed();
    }
}
