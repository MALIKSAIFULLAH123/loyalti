<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Support\ResourcePermission;

/**
 * Class QuizItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class QuizItem extends JsonResource
{
    use HasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsFriendTrait;
    use IsLikedTrait;

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

        $shortDescription = $text = '';
        $quizText         = $this->resource->quizText;
        if ($quizText) {
            $shortDescription = parse_output()->getDescription($quizText->text_parsed);
            $text             = parse_output()->parseItemDescription($quizText->text_parsed);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => $shortDescription,
            'is_sponsor'        => (bool) $this->resource->is_sponsor,
            'is_featured'       => (bool) $this->resource->is_featured,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_pending'        => !$this->resource->is_approved,
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'text'              => $text,
            'view_id'           => $this->resource->view_id,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'image'             => $this->resource->images,
            'statistic'         => $this->getStatistic(),
            'privacy'           => $this->resource->privacy,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'is_sponsored_feed' => (bool) $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getCustomExtra(),
            // 'feed_param'        => $this->getFeedParams(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_comment'    => $this->resource->total_comment, // @todo improve or remove.
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_attachment' => $this->resource->total_attachment,
            'total_play'       => $this->resource->total_play,
        ];
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getCustomExtra(): array
    {
        $extras = $this->getExtra();

        $context = user();

        return array_merge($extras, [
            ResourcePermission::CAN_PLAY => $context->can('play', [Model::class, $this->resource]),
        ]);
    }
}
