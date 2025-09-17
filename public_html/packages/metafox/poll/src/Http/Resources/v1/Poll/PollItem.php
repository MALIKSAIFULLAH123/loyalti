<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Poll\Models\Poll as Model;

/**
 * Class PollItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PollItem extends PollDetail
{
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
        $context = user();

        $shortDescription = '';
        if ($this->resource->pollText) {
            $shortDescription = parse_output()->getDescription($this->resource->pollText->text_parsed);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'question'          => ban_word()->clean($this->resource->question),
            'description'       => $shortDescription,
            'module_id'         => $this->resource->entityType(),
            'image'             => $this->resource->images,
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'view_id'           => $this->resource->view_id,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_user_voted'     => $this->isUserVoted($context),
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_closed'         => $this->resource->is_closed,
            'close_time'        => $this->resource->closed_at,
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_pending'        => !$this->resource->is_approved,
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'statistic'         => $this->getStatistic(),
            'privacy'           => $this->resource->privacy,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getPollExtra(),
            // 'feed_param'        => $this->getFeedParams(),
        ];
    }
}
