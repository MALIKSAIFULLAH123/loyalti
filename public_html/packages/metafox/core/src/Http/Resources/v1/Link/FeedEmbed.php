<?php

namespace MetaFox\Core\Http\Resources\v1\Link;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Models\Link as Model;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
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
 * Class LinkEmbed.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FeedEmbed extends JsonResource
{
    use ShareFeedInfoTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();
        $ownerResource = null;
        $taggedFriends = $this->getTaggedFriendItems($this->resource, 3);

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        $description = $this->resource->description;
        if ($description) {
            app('events')->dispatch('core.parse_content', [$this->resource, &$description, [
                'parse_url' => false,
            ]]);
        }

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => 'core',
            'resource_name'        => $this->resource->entityType(),
            'title'                => $this->resource->title,
            'description'          => $description,
            'image'                => $this->resource->image,
            'user'                 => ResourceGate::user($this->resource->userEntity),
            'parent_user'          => $ownerResource,
            'info'                 => 'user_posted_a_post_on_timeline',
            'link'                 => $this->resource->link,
            'feed_link'            => $this->resource->toLink(),
            'has_embed'            => $this->resource->has_embed,
            'host'                 => $this->resource->host,
            'is_preview_hidden'    => $this->resource->is_preview_hidden,
            'status'               => $this->toFeedContent($this->resource),
            'location'             => $this->toLocation($this->resource),
            'tagged_friends'       => new UserEntityCollection($taggedFriends),
            'total_friends_tagged' => $this->resource->total_tag_friend,
            'status_background'    => $this->resource->getBackgroundStatus(),
            'privacy'              => $this->resource->privacy,
            'from_resource'        => 'feed',
            'creation_date'        => $this->resource->created_at,
            'modification_date'    => $this->resource->updated_at,
        ];
    }
}
