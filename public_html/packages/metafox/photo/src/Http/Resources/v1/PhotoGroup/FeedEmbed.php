<?php

namespace MetaFox\Photo\Http\Resources\v1\PhotoGroup;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\PhotoGroup as Model;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Support\Facades\PhotoGroup;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/*
|--------------------------------------------------------------------------
| Resource Embed
|--------------------------------------------------------------------------
|
| Resource embed is used when you want attach this resource as embed content of
| activity feed, notification, ....
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
*/

/**
 * Class PhotoGroupEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FeedEmbed extends JsonResource
{
    use HasStatistic;
    use ShareFeedInfoTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $isLoadForEdit = $request->get('embed_object_for_edit', false);

        if($isLoadForEdit){
            [$items, $total, $remain] = PhotoGroup::getMediaItems($this->resource, $isLoadForEdit);
        }else {
            [$items, $total, $remain] = $this->resource->media_items;

            $items = $items->map(fn($x)=> ResourceGate::embed($x->detail));
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
            'remain_photo'      => $total,
            'user_id'           => $this->resource->userId(),
            'user_type'         => $this->resource->userType(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner_id'          => $this->resource->ownerId(),
            'owner_type'        => $this->resource->ownerType(),
            'photos'            => $items,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_approved'       => $this->resource->isApproved(),
            'album_id'          => $this->resource->album_id,
            'album'             => ResourceGate::detail($this->resource->album, false),
            'statistic'         => $this->getStatistic(),
            'parent_user'       => $ownerResource,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'privacy'           => $this->resource->privacy,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
        ], $this->getSharedFeedInfos());
    }

    private function getSharedFeedInfos(): array
    {
        $taggedFriends = $this->getTaggedFriendItems($this->resource, 3);

        return [
            'info'                 => $this->getFeedInfo(),
            'status'               => $this->toFeedContent($this->resource),
            'location'             => $this->toLocation($this->resource),
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->resource->total_tag_friend,
            'from_resource'        => $this->resource->activity_feed?->from_resource ?? 'app',
        ];
    }

    private function getFeedInfo(): string
    {
        $info = 'user_posted_a_post_on_timeline';

        if ($this->resource->album_id > 0 && $this->resource->album instanceof Album && !$this->resource->album->is_default) {
            $info = __p('photo::phrase.added_total_photo_and_total_video_to_the_album', [
                'total_photo' => $this->resource->statistic?->total_photo,
                'total_video' => $this->resource->statistic?->total_video,
                'album_name'  => $this->resource->album_name,
                'album_link'  => $this->resource->album_link,
            ]);
        }

        return $info;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $default = [
            'total_like'    => $this->resource->total_like,
            'total_comment' => $this->resource->total_comment,
            'total_view'    => $this->resource->total_view,
            'total_photo'   => 0,
            'total_item'    => $this->resource->total_item,
            'total_video'   => 0,
        ];

        return array_merge($default, $this->resource->statistic?->toAggregateData() ?? []);
    }
}
