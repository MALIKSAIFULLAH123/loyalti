<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;
use MetaFox\Video\Http\Resources\v1\Video\Traits\HasExtra as VideoHasExtra;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Support\Browse\Traits\Video\HandleContentTrait;
use MetaFox\Video\Support\Facade\Video as VideoFacade;

/**
 * Class VideoEmbed.
 * @property Video $resource
 */
class FeedEmbed extends JsonResource
{
    use HasStatistic;
    use HandleContentTrait;
    use ShareFeedInfoTrait;
    use VideoHasExtra;

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
        $context = user();
        $content = match ($this->resource->group_id > 0) {
            true  => $this->handleContentForUpload(),
            false => $this->handleContentForLink(),
        };

        if ($this->resource->group_id == 0) {
            if (is_string($content)) {
                $content = $this->getTransformContent($content);
            }
        }

        $postOnOther = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return array_merge([
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'is_processing'     => $this->resource->is_processing,
            'is_success'        => $this->resource->is_success,
            'is_failed'         => $this->resource->is_failed,
            'image'             => $this->resource->images,
            'destination'       => $this->resource->video_path,
            'title'             => ban_word()->clean($this->resource->title),
            'description'       => parse_output()->getDescription($content),
            'embed_code'        => $this->resource->embed_code,
            'video_url'         => $this->resource->video_url,
            'duration'          => $this->resource->duration,
            'resolution_x'      => $this->resource->resolution_x,
            'resolution_y'      => $this->resource->resolution_y,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'statistic'         => $this->getStatistic(),
            'text'              => $content,
            'parent_user'       => $ownerResource,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'privacy'           => $this->resource->privacy,
            'mature'            => $this->resource->mature,
            'extra'             => $this->getExtra(),
            'mature_config'     => VideoFacade::getMatureDataConfig($context, $this->resource),
        ], $this->getSharedFeedInfos());
    }

    private function getSharedFeedInfos(): array
    {
        $taggedFriends = $this->getTaggedFriendItems($this->resource, 3);

        return [
            'info'                 => 'added_a_video',
            'status'               => $this->toFeedContent($this->resource),
            'location'             => $this->toLocation($this->resource),
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->resource->total_tag_friend,
        ];
    }
}
