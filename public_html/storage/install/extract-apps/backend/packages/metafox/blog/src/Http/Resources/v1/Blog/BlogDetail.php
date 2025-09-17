<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Blog\Models\Blog;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class BlogDetail.
 * @property Blog $resource
 */
class BlogDetail extends JsonResource
{
    use HasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use IsFriendTrait;
    use HasHashtagTextTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'       => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'      => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
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

        $shortDescription = $text = '';

        if ($this->resource->blogText) {
            $shortDescription = parse_output()->getDescription($this->resource->blogText->text_parsed);
            $text             = $this->getTransformContent($this->resource->blogText->text_parsed);
            $text             = parse_output()->parseItemDescription($text);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => $shortDescription,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'is_approved'       => $isApproved,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_pending'        => $isPending,
            'is_draft'          => $isDraft,
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'post_status'       => $this->resource->is_draft ? Blog::BLOG_STATUS_DRAFT : Blog::BLOG_STATUS_PUBLIC,
            'text'              => $text,
            'image'             => $this->resource->images,
            'statistic'         => $this->getStatistic(),
            'privacy'           => $this->resource->privacy,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'categories'        => ResourceGate::embeds($this->resource->categories, false),
            'tags'              => $this->resource->tags,
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getExtra(),
            'feed_param'        => $this->getFeedParams(),
            'info'              => 'added_a_blog',
        ];
    }
}
