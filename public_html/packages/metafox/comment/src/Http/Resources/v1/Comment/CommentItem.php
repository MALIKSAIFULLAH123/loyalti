<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Comment\Http\Resources\v1\CommentAttachment\CommentAttachmentDetail;
use MetaFox\Comment\Models\Comment as Model;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Support\Helper;
use MetaFox\Comment\Support\Traits\HasCommentExtraTrait;
use MetaFox\Comment\Traits\HasTransformContent;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\UserReactedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class CommentItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CommentItem extends JsonResource
{
    use HasStatistic;
    use HasCommentExtraTrait;
    use IsLikedTrait;
    use UserReactedTrait;
    use HasTransformContent;

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_like'    => $this->resource->total_like,
            'total_comment' => $this->resource->total_comment, // @todo improve or remove.
        ];
    }

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
        $context   = user();

        $extraData = [];

        $isHidden  = $this->resource->is_hidden || $this->resource?->parentComment?->is_hidden;

        if ($this->resource->commentAttachment) {
            $extraData = new CommentAttachmentDetail($this->resource->commentAttachment);
        }

        $return = [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'parent_id'         => $this->resource->parent_id,
            'item_id'           => $this->resource->itemId(),
            'item_type'         => $this->resource->itemType(),
            'like_type_id'      => $this->resource->entityType(),
            'like_item_id'      => $this->resource->entityId(),
            'comment_type_id'   => $this->resource->itemType(),
            'comment_item_id'   => $this->resource->itemId(),
            'child_total'       => $this->resource->total_comment,
            'children'          => $this->getReplies($context),
            'is_liked'          => $this->isLike($context, $this->resource),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'text'              => ban_word()->clean($this->getTransformContent()),
            'text_parsed'       => ban_word()->clean($this->resource->text_parsed),
            'is_approved'       => $this->resource->is_approved,
            'is_pending'        => !$this->resource->is_approved,
            'extra_data'        => $extraData,
            'user_reacted'      => $this->userReacted($context, $this->resource),
            'most_reactions'    => $this->userMostReactions($context, $this->resource),
            'most_reactions_information' => $this->getItemReactionAggregation($context, $this->resource),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'statistic'         => $this->getStatistic(),
            'extra'             => $this->getExtra(),
            'is_hidden'         => $isHidden,
            'link'              => $this->resource->toLink(),
            'is_edited'         => $this->resource->is_edited,
            'role_label'        => $this->getRoleLabelInOwner($this->resource->user, $this->resource->item->owner),
        ];

        if (is_array($this->resource->reply_detail_statistics)) {
            $return = array_merge($return, [
                'reply_detail_statistics' => $this->resource->reply_detail_statistics,
                'relevant_children'       => new CommentItemCollection($this->resource->relevant_children),
            ]);
        }

        return $return;
    }

    /**
     * @param string|null $text
     * @return string|null
     */
    protected function handleTextForView(?string $text): ?string
    {
        if (!is_string($text)) {
            return null;
        }

        return str_replace(['&amp;'], ['&'], $text);
    }

    /**
     * @param  UserContract|null $user
     * @param  UserContract|null $owner
     * @return string|null
     */
    protected function getRoleLabelInOwner(?UserContract $user, ?UserContract $owner): ?string
    {
        if (!$user instanceof UserContract) {
            return null;
        }

        if ($owner instanceof HasPrivacyMember) {
            return $owner->getRoleLabel($user);
        }

        return null;
    }

    protected function getReplies(UserContract $context): JsonResource
    {
        if (!Helper::isShowReply() || MetaFox::isMobile()) {
            return new CommentItemCollection([]);
        }

        if (!$this->resource->relationLoaded('children')) {
            $limitReplies = Settings::get('comment.prefetch_replies_on_feed');

            $children     = $this->commentRepository()->getReplies($this->resource, $context, $limitReplies);

            return new CommentItemCollection($children);
        }

        return new CommentItemCollection($this->resource->children);
    }

    protected function commentRepository(): CommentRepositoryInterface
    {
        return resolve(CommentRepositoryInterface::class);
    }
}
