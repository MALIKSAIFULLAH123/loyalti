<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class LiveVideoDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class LiveVideoDetail extends JsonResource
{
    use HasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use RepoTrait;
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
        $context    = user();
        $isApproved = $this->resource->is_approved;
        $isPending  = false;
        if (!$isApproved) {
            $isPending = true;
        }

        $shortDescription = $text = '';
        if ($this->resource->liveVideoText) {
            $shortDescription = parse_output()->getDescription($this->resource->liveVideoText->text);
            $text             = $this->getTransformContent($this->resource->liveVideoText->text);
            $text             = parse_output()->parseItemDescription($text);
        }

        $repository = $this->getLiveVideoRepository();

        return array_merge([
            'id'                    => $this->resource->entityId(),
            'module_name'           => $this->resource->moduleName(),
            'resource_name'         => $this->resource->entityType(),
            'title'                 => ban_word()->clean($this->resource->title),
            'duration'              => $this->getLiveVideoRepository()->getDuration($this->resource),
            'description'           => ban_word()->clean($shortDescription),
            'module_id'             => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'               => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'is_approved'           => $isApproved,
            'is_sponsor'            => $this->resource->is_sponsor,
            'is_streaming'          => $this->resource->is_streaming,
            'is_featured'           => $this->resource->is_featured,
            'is_landscape'          => $this->resource->is_landscape,
            'stream_key'            => $this->resource->stream_key,
            'is_liked'              => $this->isLike($context, $this->resource),
            'is_friend'             => $this->isFriend($context, $this->resource->user),
            'is_pending'            => $isPending,
            'is_saved'              => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'statistic'             => $this->getLiveVideoStatistic(),
            'privacy'               => $this->resource->privacy,
            'user'                  => ResourceGate::user($this->resource->userEntity),
            'owner'                 => ResourceGate::user($this->resource->ownerEntity),
            'is_owner'              => $this->resource->userId() == $context->entityId(),
            'tags'                  => $this->resource->tags,
            'attachments'           => [],
            'is_sponsored_feed'     => $this->resource->sponsor_in_feed,
            'creation_date'         => $this->resource->created_at,
            'modification_date'     => $this->resource->updated_at,
            'link'                  => $this->resource->toLink(),
            'url'                   => $this->resource->toUrl(),
            'extra'                 => $this->getExtra(),
            'feed_param'            => $this->getFeedParams(),
            'playback'              => $this->resource->playback,
            'video_url'             => $repository->getVideoPlayback($this->resource->entityId()),
            'thumbnail_url'         => $repository->getThumbnailPlayback($this->resource->entityId()),
            'is_off_notification'   => $this->resource->isOffNotification(),
            'text'                  => ban_word()->clean($text),
            'owner_navigation_link' => $this->resource->owner_navigation_link,
            'mode'                  => $this->resource->live_type,
            'webcamConfig'          => $this->resource->webcam_config !== null ? $this->getWebcamConfig() : null,
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

    protected function getWebcamConfig(): array
    {
        $webcamConfig         = $this->resource->webcam_config;
        $dataWebcamConfig     = json_decode($webcamConfig, true);

        return [
            'video' => $dataWebcamConfig['video'] ?? null,
            'audio' => $dataWebcamConfig['audio'] ?? null,
        ];
    }
}
