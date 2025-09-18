<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Video\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Video\Http\Resources\v1\Video\Traits\HasExtra as VideoHasExtra;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Support\Browse\Traits\Video\HandleContentTrait;
use MetaFox\Video\Support\Facade\Video as VideoFacade;

/**
 * Class VideoItem.
 *
 * @property Video $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class VideoItem extends JsonResource
{
    use HasStatistic;
    use VideoHasExtra;
    use IsLikedTrait;
    use HandleContentTrait;
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
        $context = user();

        $text = match ($this->resource->group_id > 0) {
            true  => $this->handleContentForUpload(),
            false => $this->handleContentForLink(),
        };

        if ($text) {
            $modelContent = $this->resource->group_id > 0 ? $this->resource->group : $this->resource;
            app('events')->dispatch('core.parse_content', [$modelContent, &$text]);
        }

        $shortDescription = $text;

        if ($this->resource->group_id == 0) {
            if (is_string($text)) {
                $text = $this->getTransformContent($text);
                $text = parse_output()->parseItemDescription($text);
            }
        }

        if (null !== $shortDescription) {
            $shortDescription = parse_output()->getDescription($shortDescription);
        }

        $mature = $this->resource->mature;

        return [
            'id'                => $this->resource->entityId(),
            'album_id'          => $this->resource->album_id,
            'album'             => ResourceGate::asResource($this->resource->album, 'embed'),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->title),
            'text'              => $text,
            'description'       => $shortDescription,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'is_processing'     => $this->resource->is_processing,
            'is_success'        => $this->resource->is_success,
            'is_failed'         => $this->resource->is_failed,
            'privacy'           => $this->resource->privacy,
            'is_liked'          => $this->isLike($context, $this->resource->reactItem()),
            'is_pending'        => !$this->resource->is_approved,
            'is_saved'          => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'destination'       => VideoFacade::getDataWithContext($context, $this->resource, 'destination'),
            'duration'          => $this->resource->duration,
            'video_url'         => VideoFacade::getDataWithContext($context, $this->resource, 'video_url'),
            'embed_code'        => $this->resource->embed_code,
            'image'             => VideoFacade::getDataWithContext($context, $this->resource),
            'statistic'         => $this->getStatistic(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'slug'              => $this->resource->toSlug(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'categories'        => new CategoryItemCollection($this->resource->categories),
            'creation_date'     => $this->resource->created_at,
            'extra'             => $this->getExtra(),
            'is_creator'        => $context->entityId() == $this->resource->userId(),
            'mature'            => $mature,
            'mature_config'     => VideoFacade::getMatureDataConfig($context, $this->resource),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'    => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_comment' => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0, // @todo improve or remove.
            'total_share'   => $this->resource->total_share,
            'total_view'    => $this->resource->total_view,
            'total_reply'   => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_rating'  => $this->resource->total_rating,
            'total_score'   => $this->resource->total_score,
        ];
    }
}
