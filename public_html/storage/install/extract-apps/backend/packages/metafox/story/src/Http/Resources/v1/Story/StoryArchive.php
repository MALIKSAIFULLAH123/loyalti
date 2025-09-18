<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Story\Models\Story as Model;
use MetaFox\Story\Support\Facades\StoryFacades;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StoryArchive.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StoryArchive extends StoryDetail
{
    use HasExtra;
    use HasStatistic;
    use IsFriendTrait;
    use IsLikedTrait;
    use HasHashtagTextTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $storyText = $this->resource->storyText;
        $context   = user();
        $text      = '';

        if ($storyText) {
            $text = $this->getTransformContent($storyText->text_parsed);
            $text = parse_output()->parse($text);
        }
        $reactItem   = $this->resource->reactItem();
        $extraParams = $this->getExtraParams();

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'is_owner'          => $this->resource->isOwner($context),
            'in_process'        => $this->resource->in_process,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_approved'       => $this->resource->isApproved(),
            'is_archive'        => $this->resource->is_archive,
            'view_id'           => $this->resource->view_id,
            'tags'              => [], //Todo: add hashtag
            'text'              => $text,
            'type'              => $this->resource->type,
            'content'           => $this->resource->toTitle(),
            'privacy'           => $this->resource->privacy,
            'duration'          => Arr::get($extraParams, 'duration') ?? $this->resource->duration,
            'has_seen'          => StoryFacades::hasSeen($context, $this->resource),
            'expired_at'        => $this->resource->expired_at,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'background'        => ResourceGate::embed($this->resource->storyBackground, false),
            'image'             => $this->getImages(),
            'thumbs'            => $this->resource->thumbnails,
            'video'             => $this->resource->video,
            'statistic'         => $this->getStatistic(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'extra'             => $this->getExtra(),
            'extra_params'      => $extraParams,
            'related_comments'  => $this->relatedCommentsItemDetail($context, $this->resource),
            'comment_item_id'   => $reactItem->entityId(),
            'comment_type_id'   => $reactItem->entityType(),
            'url'               => $this->resource->toUrl(),
            'link'              => $this->resource->toLink(),
            'relevant_comments' => $this->when($request->get('comment_id', false), $this->getRelevantComments()),
        ];
    }
}
