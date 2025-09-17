<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Support\Browse\Traits\ForumThread\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\ForumThread\StatisticTrait;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

class ForumThreadDetail extends JsonResource
{
    use HasHashtagTextTrait;
    use StatisticTrait;
    use ExtraTrait;
    use HasFeedParam;
    use IsLikedTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = $this->resource;

        $context = user();

        $userEntity = new UserEntityDetail($resource->userEntity);

        $isApproved = (bool) $resource->isApproved();

        $description = null;

        if (null !== $resource->description) {
            $description = $this->getTransformContent($resource->description->text_parsed);
            $description = parse_output()->parseItemDescription($description);
        }

        $attachments = ResourceGate::items($resource->getAttachments(), false);

        $forumResource = null;

        if (null !== $resource->forum) {
            $forumResource = ResourceGate::asEmbed($resource->forum, null);
        }

        $itemResource = null;

        if (null !== $resource->item_type && $resource->item_id > 0) {
            $item = $resource->getItem();

            if (null !== $item) {
                $itemResource = ResourceGate::asResource($item->refresh(), 'integration');
            }
        }

        $title = $this->handleTitle($resource->toTitle());
        $owner = ResourceGate::user($this->resource->ownerEntity);

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'title'             => $title,
            'description'       => $description,
            'short_description' => $resource->short_description,
            'forum'             => $forumResource,
            'user'              => $userEntity,
            'owner'             => $owner,
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'item'              => $itemResource,
            'tags'              => $resource->getTags(),
            'attachments'       => $attachments,
            'statistic'         => $this->getStatistic(),
            'is_closed'         => $resource->isClosed(),
            'is_wiki'           => $resource->isWiki(),
            'is_sticked'        => $resource->isSticked(),
            'is_subscribed'     => $resource->isSubscribed(),
            'is_viewed'         => $resource->isViewed(),
            'is_approved'       => $isApproved,
            'is_pending'        => !$isApproved,
            'is_sponsor'        => $resource->isSponsor(),
            'is_saved'          => $resource->isSaved(),
            'is_liked'          => $this->isLike($context, $resource),
            'feed_param'        => $this->getFeedParams(),
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
            'creation_date'     => $this->convertDate($resource->getCreatedAt()),
            'modification_date' => $this->convertDate($resource->getUpdatedAt()),
            'extra'             => $this->getThreadExtra(),
            'info'              => 'added_a_thread',
        ];
    }

    protected function convertDate(?string $date): ?string
    {
        if (null == $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }

    protected function handleTitle(string $title): string
    {
        $title = ban_word()->clean($title);
        $title = ban_word()->parse($title);

        return $title;
    }
}
