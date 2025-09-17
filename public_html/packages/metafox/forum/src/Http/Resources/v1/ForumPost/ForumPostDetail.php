<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Models\ForumPostText;
use MetaFox\Forum\Support\Browse\Traits\ForumPost\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\ForumPost\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;
use MetaFox\User\Support\Facades\UserEntity;

class ForumPostDetail extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;
    use IsLikedTrait;
    use HasFeedParam;

    public function toArray($request): array
    {
        $resource = $this->resource;

        $content = '';

        $postText = $resource->postText;

        if ($postText instanceof ForumPostText) {
            $content = parse_output()->parseItemDescription($postText->text_parsed);
        }

        $isApproved = $resource->isApproved();

        $userEntity = new UserEntityDetail($resource->userEntity);

        $thread = ResourceGate::asEmbed($resource->thread);

        $context = user();

        $attachments = ResourceGate::items($resource->attachments, false);

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'user'              => $userEntity,
            'thread'            => $thread,
            'short_content'     => $resource->short_content,
            'content'           => $content,
            'is_saved'          => $resource->isSaved(),
            'is_approved'       => $isApproved,
            'is_liked'          => $this->isLike($context, $resource),
            'feed_param'        => $this->getFeedParams(),
            'quote_content'     => $this->getQuotedContent(),
            'quote_user'        => $this->getQuotedUser(),
            'quote_post'        => $this->getQuotedPost(),
            'attachments'       => $attachments,
            'url'               => $resource->toUrl(),
            'link'              => $resource->toLink(),
            'creation_date'     => $this->convertDate($resource->created_at),
            'modification_date' => $this->convertDate($resource->updated_at),
            'statistic'         => $this->getStatistic(),
            'extra'             => $this->getPostExtra(),
            'info'              => 'added_a_post',
        ];
    }

    protected function getQuotedUser(): ?JsonResource
    {
        $quoteData = $this->resource->quoteData;

        if (null === $quoteData) {
            return null;
        }

        if (null === $quoteData->quotedUser) {
            return null;
        }

        $userEntity = UserEntity::getById($quoteData->quotedUser->entityId());

        return new UserEntityDetail($userEntity);
    }

    protected function getQuotedContent(): ?string
    {
        $quoteData = $this->resource->quoteData;

        if (null === $quoteData) {
            return null;
        }

        if (null === $quoteData->quote_content) {
            return null;
        }

        return parse_output()->parseItemDescription($quoteData->quote_content);
    }

    protected function getQuotedPost(): ?JsonResource
    {
        $quotePost = $this->resource->getQuotePost();

        if (null === $quotePost) {
            return null;
        }

        return ResourceGate::asEmbed($quotePost);
    }

    protected function convertDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }
}
