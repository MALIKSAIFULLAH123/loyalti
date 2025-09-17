<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread as Model;
use MetaFox\Forum\Support\Browse\Traits\ForumThread\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\ForumThread\StatisticTrait;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/**
 * Class GroupItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumThreadItem extends ForumThreadDetail
{
    use HasHashtagTextTrait;
    use StatisticTrait;
    use ExtraTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = $this->resource;

        $userEntity = null;

        if (null !== $resource->userEntity) {
            $userEntity = new UserEntityDetail($resource->userEntity);
        }

        $isApproved = (bool) $resource->isApproved();

        $description = null;

        if (null !== $resource->description) {
            $description = $this->getTransformContent($resource->description->text_parsed);
            $description = parse_output()->parseItemDescription($description);
        }

        $forumResource = null;

        if (null !== $resource->forum) {
            $forumResource = ResourceGate::asEmbed($resource->forum, null);
        }

        $attachments = ResourceGate::items($resource->getAttachments(), false);

        $title = $this->handleTitle($resource->toTitle());

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'title'             => $title,
            'description'       => $description,
            'short_description' => $resource->short_description,
            'forum'             => $forumResource,
            'user'              => $userEntity,
            'tags'              => $resource->getTags(),
            'attachments'       => $attachments,
            'statistic'         => $this->getStatistic(),
            'is_closed'         => $resource->isClosed(),
            'is_wiki'           => $resource->isWiki(),
            'is_sticked'        => $resource->isSticked(),
            'is_subscribed'     => $resource->isSubscribed(),
            'is_viewed'         => $resource->isViewed(),
            'is_saved'          => $resource->isSaved(),
            'is_approved'       => $isApproved,
            'is_pending'        => !$isApproved,
            'is_sponsor'        => $resource->isSponsor(),
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
            'last_post'         => $this->getLatestPost(),
            'creation_date'     => $this->convertDate($resource->getCreatedAt()),
            'modification_date' => $this->convertDate($resource->getUpdatedAt()),
            'extra'             => $this->getThreadExtra(),
        ];
    }

    protected function getLatestPost(): ?JsonResource
    {
        if (!$this->resource->lastListingPost instanceof ForumPost) {
            return null;
        }

        return ResourceGate::asEmbed($this->resource->lastListingPost);
    }
}
