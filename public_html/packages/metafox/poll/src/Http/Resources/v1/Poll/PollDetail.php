<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Poll\Http\Resources\v1\Answer\AnswerItemCollection;
use MetaFox\Poll\Http\Resources\v1\Design\DesignDetail;
use MetaFox\Poll\Http\Resources\v1\Traits\IsUserVoted;
use MetaFox\Poll\Http\Resources\v1\Traits\PollHasExtra;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

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
 * Class PollDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PollDetail extends JsonResource
{
    use PollHasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use IsUserVoted;
    use ShareFeedInfoTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context  = user();
        $pollText = $this->resource->pollText;

        $description = $text = '';
        if (null !== $pollText) {
            $description = parse_output()->getDescription($pollText->text_parsed);
            $text        = $this->getTransformContent($pollText->text_parsed);
            $text        = parse_output()->parseItemDescription($text);
        }

        return array_merge([
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'question'      => ban_word()->clean($this->resource->question),
            'description'   => $description,
            'text'          => $text,
            'module_id'     => $this->resource->entityType(),
            'item_id'       => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'view_id'       => $this->resource->view_id,
            'design'        => new DesignDetail($this->resource->design),
            'answers'       => new AnswerItemCollection($this->resource->answers),
            'is_featured'   => $this->resource->is_featured,
            'is_sponsor'    => $this->resource->is_sponsor,
            'is_user_voted' => $this->isUserVoted($context),
            'is_liked'      => $this->isLike($context, $this->resource),
            'is_closed'     => $this->resource->is_closed,
            'close_time'    => $this->resource->closed_at,
            'is_friend'     => $this->isFriend($context, $this->resource->user),
            'is_pending'    => !$this->resource->is_approved,
            'is_saved'      => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'is_multiple'       => $this->resource->is_multiple,
            'public_vote'       => $this->resource->public_vote,
            'image'             => $this->resource->images,
            'statistic'         => $this->getStatistic(),
            'privacy'           => $this->resource->privacy,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getPollExtra(),
            'feed_param'        => $this->getFeedParams(),

        ], $this->getSharedFeedInfos());
    }

    protected function getSharedFeedInfos(): array
    {
        $taggedFriends = $this->getTaggedFriendItems($this->resource, 3);

        return [
            'info'                 => 'added_a_poll',
            'status'               => $this->toFeedContent($this->resource),
            'location'             => $this->toLocation($this->resource),
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->resource->total_tag_friend,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'       => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'       => $this->resource->total_view,
            'total_comment'    => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'      => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_attachment' => $this->resource->total_attachment,
            'total_vote'       => $this->resource->total_vote,
            'total_share'      => $this->resource->total_share,
        ];
    }
}
