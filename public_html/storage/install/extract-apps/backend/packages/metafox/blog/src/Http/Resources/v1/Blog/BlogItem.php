<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Blog\Models\Blog;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class BlogItem.
 * @property Blog $resource
 */
class BlogItem extends JsonResource
{
    use HasStatistic;
    use HasExtra;
    use IsLikedTrait;
    use IsFriendTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $this->resource->total_comment, // @todo improve or remove.
            'total_attachment' => $this->resource->total_attachment,
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context    = user();
        $isDraft    = $this->resource->is_draft;
        $isApproved = $this->resource->is_approved;
        $isPending  = false;
        if (!$isDraft) {
            if (!$isApproved) {
                $isPending = true;
            }
        }

        $shortDescription = '';
        if ($this->resource->blogText) {
            $shortDescription = parse_output()->getDescription($this->resource->blogText->text_parsed);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => $shortDescription,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'privacy'           => $this->resource->privacy,
            'image'             => $this->resource->images,
            'is_approved'       => $isApproved,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'is_featured'       => $this->resource->is_featured,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_pending'        => $isPending,
            'is_draft'          => $isDraft,
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'post_status'       => $this->resource->is_draft ? Blog::BLOG_STATUS_DRAFT : Blog::BLOG_STATUS_PUBLIC,
            'tags'              => $this->resource->tags,
            'statistic'         => $this->getStatistic(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'categories'        => ResourceGate::embeds($this->resource->categories),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'extra'             => $this->getExtra(),
        ];
    }
}
