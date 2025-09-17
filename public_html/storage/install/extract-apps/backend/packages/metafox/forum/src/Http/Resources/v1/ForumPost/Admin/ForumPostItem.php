<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Models\ForumPost as Model;
use MetaFox\Forum\Support\Browse\Traits\ForumPost\ExtraTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;
use MetaFox\User\Support\Facades\UserEntity;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ForumPostItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ForumPostItem extends JsonResource
{
    use ExtraTrait;

    public function toArray($request): array
    {
        $resource   = $this->resource;
        $userEntity = new UserEntityDetail($resource->userEntity);
        $thread     = ResourceGate::embed($resource->thread);

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'user'              => $userEntity,
            'thread'            => $thread,
            'short_content'     => $resource->short_content,
            'is_approved'       => $resource->isApproved(),
            'quote_content'     => $this->getQuotedContent(),
            'quote_user'        => $this->getQuotedUser(),
            'quote_post'        => $this->getQuotedPost(),
            'url'               => $resource->toUrl(),
            'link'              => $resource->toLink(),
            'creation_date'     => $this->convertDate($resource->created_at),
            'modification_date' => $this->convertDate($resource->updated_at),
            'extra'             => $this->getPostExtra(),
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
