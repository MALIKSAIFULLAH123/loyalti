<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/**
 * Class LiveVideoEmbed.
 * @property LiveVideo $resource
 */
class FeedEmbed extends JsonResource
{
    use HasStatistic;
    use RepoTrait;
    use IsLikedTrait;
    use HasExtra;
    use ShareFeedInfoTrait;

    protected function getLiveVideoStatistic(): array
    {
        $statistic                 = $this->getStatistic();
        $statistic['total_viewer'] = $this->resource->total_viewer;

        return $statistic;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function toArray($request): array
    {
        $shortDescription = '';
        if ($this->resource->liveVideoText) {
            $shortDescription = parse_output()->getDescription($this->resource->liveVideoText->text_parsed);
        }

        $context    = user();
        $isApproved = $this->resource->is_approved;

        $isPending = false;
        if (!$isApproved) {
            $isPending = true;
        }

        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();
        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return array_merge([
            'id'                  => $this->resource->entityId(),
            'module_name'         => $this->resource->moduleName(),
            'resource_name'       => $this->resource->entityType(),
            'title'               => ban_word()->clean($this->resource->title),
            'duration'            => $this->getLiveVideoRepository()->getDuration($this->resource),
            'description'         => ban_word()->clean($shortDescription),
            'module_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'             => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'privacy'             => $this->resource->privacy,
            'is_approved'         => $isApproved,
            'is_sponsor'          => $this->resource->is_sponsor,
            'is_sponsored_feed'   => $this->resource->sponsor_in_feed,
            'is_featured'         => $this->resource->is_featured,
            'is_streaming'        => $this->resource->is_streaming,
            'is_landscape'        => $this->resource->is_landscape,
            'stream_key'          => $this->resource->stream_key,
            'is_liked'            => $this->isLike($context, $this->resource),
            'is_friend'           => $this->isFriend($context, $this->resource->user),
            'is_pending'          => $isPending,
            'is_saved'            => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'tags'                => $this->resource->tags,
            'attachments'         => [], //Todo: add attachments
            'statistic'           => $this->getLiveVideoStatistic(),
            'user'                => ResourceGate::user($this->resource->userEntity),
            'owner'               => ResourceGate::user($this->resource->ownerEntity),
            'is_owner'            => $this->resource->userId() == $context->entityId(),
            'link'                => $this->resource->toLink(),
            'url'                 => $this->resource->toUrl(),
            'creation_date'       => $this->resource->created_at,
            'modification_date'   => $this->resource->updated_at,
            'extra'               => $this->getExtra(),
            'playback'            => $this->resource->playback,
            'video_url'           => $this->getLiveVideoRepository()->getVideoPlayback($this->resource->entityId()),
            'thumbnail_url'       => $this->getLiveVideoRepository()->getThumbnailPlayback($this->resource->entityId()),
            'is_off_notification' => $this->resource->isOffNotification(),
            'parent_user'         => $ownerResource,
        ], $this->getSharedFeedInfos());
    }

    private function getSharedFeedInfos(): array
    {
        $taggedFriends = $this->getLiveVideoRepository()->getTaggedFriends(user(), $this->resource);

        return [
            'is_show_location'     => false,
            'info'                 => 'add_a_live_video',
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->resource->total_tag_friend,
        ];
    }
}
