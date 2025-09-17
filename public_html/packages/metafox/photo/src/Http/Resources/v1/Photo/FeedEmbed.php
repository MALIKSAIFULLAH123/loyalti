<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Traits\PhotoHasExtra;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;

/**
 * Class PhotoEmbed.
 *
 * @property Photo $resource
 */
class FeedEmbed extends JsonResource
{
    use PhotoHasExtra;
    use ShareFeedInfoTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException|AuthorizationException
     */
    public function toArray($request): array
    {
        $this->resource->loadMissing('photoInfo', 'fileItem');
        $context        = user();
        $fileItem       = $this->resource->fileItem;
        $repository     = resolve(PhotoRepositoryInterface::class);
        $taggedFriends  = $repository->getTaggedFriends($context, $this->resource->entityId());
        $isLatestAvatar = $this->resource->entityId() === $this->resource?->ownerEntity?->avatar_id;
        $postOnOther    = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null !== $this->resource->ownerEntity ? ResourceGate::user($this->resource->ownerEntity) : null;

        return array_merge([
            'id'                => $this->resource->entityId(),
            'module_name'       => Photo::ENTITY_TYPE,
            'resource_name'     => $this->resource->entityType(),
            'mature'            => $this->resource->mature,
            'width'             => $fileItem?->width,
            'height'            => $fileItem?->height,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => $ownerResource,
            'image'             => $this->resource->images,
            'avatar'            => $this->when($isLatestAvatar, $this->resource->ownerEntity?->avatars),
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_profile_photo'  => $this->resource->is_profile_photo,
            'text'              => ban_word()->clean($this->resource->content),
            'tagged_friends'    => new PhotoTaggedFriendCollection($taggedFriends),
            'extra'             => $this->getCustomExtra(),
            'parent_user'       => $postOnOther ? $ownerResource : null,
            'privacy'           => $this->resource->privacy,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
        ], $this->getSharedFeedInfos($context));
    }

    private function getSharedFeedInfos(User $context): array
    {
        $info = __p('photo::phrase.added_a_photo');

        $album = $this->resource->album;

        if ($this->resource->album_id && $album instanceof Album && !$album->is_default) {
            $info = __p('photo::phrase.shared_a_photo_from_album_name_on_owner_name', [
                'album_name' => ban_word()->clean($album->name),
                'album_link' => $album->album_link,
                'owner_link' => $album->owner_link,
                'owner_name' => $album->owner_name,
                'owner_type' => $album->owner_type,
            ]);
        }

        if ($this->resource->is_profile_photo) {
            $owner = $album->owner;
            $info  = $album->owner_type . '_user_name_updated_their_profile_photo';

            if ($owner instanceof \MetaFox\User\Models\User) {
                $info = __p('user::phrase.user_name_updated_their_profile_picture', [
                    'gender'     => $album->ownerEntity->possessive_gender,
                    'isAuthUser' => (int) ($context->entityId() == $owner->entityId()),
                ]);
            }
        }

        if ($this->resource->is_cover_photo) {
            $owner = $album->owner;
            $info  = $album->owner_type . '_user_name_updated_their_cover_photo';

            if ($owner instanceof \MetaFox\User\Models\User) {
                $info = __p('user::phrase.user_name_updated_their_cover_photo', [
                    'gender'     => $album->ownerEntity->possessive_gender,
                    'isAuthUser' => (int) ($context->entityId() == $owner->entityId()),
                ]);
            }
        }

        return [
            'info'                    => $info,
            'status'                  => $this->toFeedContent($this->resource),
            'location'                => $this->toLocation($this->resource),
            'total_friends_tagged'    => $this->resource->total_tag_friend,
            'photo_type'              => $this->resource->photo_type,
            'is_hide_tagged_headline' => $this->isHideTaggedHeadline($this->resource),
        ];
    }
}
