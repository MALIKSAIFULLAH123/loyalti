<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Share;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Traits\FeedSupport;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;
use MetaFox\User\Models\UserEntity;

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
 * Class FeedEmbed.
 * @property Feed $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FeedEmbed extends JsonResource
{
    use FeedSupport;

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
        $item = $this->resource->item;

        $userEntity   = $this->resource->userEntity;
        $ownerEntity  = $this->resource->ownerEntity;
        $user         = $this->resource->user;
        $owner        = $this->resource->owner;
        $userResource = ResourceGate::user($userEntity);

        $postOnOther = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;
        if ($postOnOther) {
            $ownerResource = ResourceGate::user($ownerEntity);
        }

        $actionPhrase        = resolve(TypeManager::class)->getTypePhraseWithContext($this->resource);
        $actionItem          = $reactItem = $this->getActionResource();

        if ($actionItem instanceof Content) {
            $reactItem = $actionItem->reactItem();
        }

        $context = user();

        $data = [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'type_id'              => $this->resource->type_id,
            'like_type_id'         => $reactItem->entityType(),
            'like_item_id'         => $reactItem->entityId(),
            'comment_type_id'      => $reactItem->entityType(),
            'comment_item_id'      => $reactItem->entityId(),
            'item_type'            => $this->resource->itemType(),
            'item_id'              => $this->resource->itemId(),
            'info'                 => $actionPhrase,
            'status'               => $this->getParsedContent(),
            'tagged_friends'       => new UserEntityCollection($this->getTaggedFriendsForFeed()),
            'total_friends_tagged' => $actionItem instanceof HasTaggedFriend ? $actionItem->total_tag_friend : 0,
            'location'             => $this->getLocation(),
            'statistic'            => $this->getStatistic(),
            'user'                 => $userResource,
            'is_sponsor'           => $this->resource->is_sponsor,
            'embed_object'         => ResourceGate::asEmbed($item),
            'parent_user'          => $ownerResource,
            'role_label'           => $this->getRoleLabelInOwner($user, $owner),
            'privacy'              => $this->resource->privacy,
            'user_full_name'       => $userEntity instanceof UserEntity ? $userEntity->name : null,
            'owner_full_name'      => $ownerEntity instanceof UserEntity ? $ownerEntity->name : null,
            'creation_date'        => $this->resource->created_at,
            'modification_date'    => $this->resource->updated_at,
            'link'                 => $this->resource->toLink(),
            'url'                  => $this->resource->toUrl(),
            'extra'                => $this->getFeedExtra(),
            'status_background'    => $this->getBackgroundStatus(),
            'privacy_detail'       => ActivityFeed::getPrivacyDetail($context, $this->resource, $owner?->getRepresentativePrivacy()),
            'from_resource'        => $this->resource->from_resource,
        ];

        if ($item instanceof Share) {
            $item->loadMissing(['item']);
            $content      = $item->item;
            $data['link'] = $this->resource->toLink();
            $data['url']  = $this->resource->toUrl();

            if ($content instanceof Content) {
                $data['link'] = $content->toLink();
                $data['url']  = $content->toUrl();
            }
        }

        return $data;
    }
}
