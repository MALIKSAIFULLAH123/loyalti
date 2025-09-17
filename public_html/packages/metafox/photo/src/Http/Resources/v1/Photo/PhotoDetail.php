<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Facades\Album as AlbumContract;
use MetaFox\Photo\Support\Facades\Photo as PhotoFacade;
use MetaFox\Photo\Support\Traits\PhotoHasExtra;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Helpers\ShareFeedInfoTrait;
use MetaFox\Platform\Traits\Helpers\UserReactedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class PhotoDetail.
 *
 * @property Model $resource
 */
class PhotoDetail extends JsonResource
{
    use PhotoHasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use UserReactedTrait;
    use ShareFeedInfoTrait;

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

        $repository = resolve(PhotoRepositoryInterface::class);

        $content = null;

        $text = $shortDescription = null;

        $reactItem = $this->resource->reactItem();

        if (null !== $this->resource->content) {
            $content = $this->resource->content;
        }

        if ($this->resource->group_id > 0 && null === $content) {
            $content = $reactItem->content;
        }

        if (null !== $content) {
            app('events')->dispatch('core.parse_content', [$this->resource, &$content]);

            $shortDescription = parse_output()->getDescription($content);

            $text = parse_output()->parse($content, true);

            $text = $this->parseHashtags($text);
        }

        $taggedFriends = $repository->getTaggedFriends($context, $this->resource->entityId());

        $fileItem = $this->resource->fileItem;

        $album = null;

        if ($this->resource->album_id > 0) {
            $isDefaultAlbum = false;

            if (null !== $this->resource->album) {
                $isDefaultAlbum = AlbumContract::isDefaultAlbum($this->resource->album->album_type);
            }

            if (!$isDefaultAlbum) {
                $album = new AlbumDetail($this->resource->album);
            }
        }

        return array_merge([
            'id'               => $this->resource->entityId(),
            'module_name'      => $this->resource->entityType(),
            'resource_name'    => $this->resource->entityType(),
            'title'            => ban_word()->clean($this->resource->title),
            'like_type_id'     => $reactItem->entityType(),
            'like_item_id'     => $reactItem->entityId(),
            'comment_type_id'  => $reactItem->entityType(),
            'comment_item_id'  => $reactItem->entityId(),
            'description'      => ban_word()->clean($shortDescription),
            'text'             => $text,
            'privacy'          => $this->resource->privacy,
            'module_id'        => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'          => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'group_id'         => $this->resource->group_id,
            'album_id'         => $this->resource->album_id,
            'type_id'          => $this->resource->type_id,
            'user'             => ResourceGate::user($this->resource->userEntity),
            'owner'            => ResourceGate::user($this->resource->ownerEntity),
            'owner_type_name'  => __p_type_key($this->resource->ownerType()),
            'is_approved'      => $this->resource->is_approved,
            'is_sponsor'       => $this->resource->is_sponsor,
            'is_featured'      => $this->resource->is_featured,
            'is_cover'         => $this->resource->is_cover,
            'is_profile_photo' => $this->resource->is_profile_photo,
            'is_cover_photo'   => $this->resource->is_cover_photo,
            'is_temp'          => $this->resource->is_temp,
            'is_friend'        => $this->isFriend($context, $this->resource->user),
            'is_liked'         => $this->isLike($context, $reactItem),
            'is_pending'       => !$this->resource->is_approved,
            'is_saved'         => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'width'                 => $fileItem->width ?? 0,
            'height'                => $fileItem?->height ?? 0,
            'file_size'             => $fileItem?->file_size ?? 0,
            'mature'                => $this->resource->mature,
            'image'                 => PhotoFacade::getImagesWithContext($context, $this->resource),
            'categories'            => ResourceGate::embeds($this->resource->categories),
            'photo_tags'            => [],
            'tagged_friends'        => new PhotoTaggedFriendCollection($taggedFriends),
            'is_sponsored_feed'     => $this->resource->sponsor_in_feed,
            'album'                 => $album,
            'creation_date'         => $this->resource->created_at,
            'modification_date'     => $this->resource->updated_at,
            'link'                  => $this->resource->toLink(),
            'url'                   => $this->resource->toUrl(),
            'slug'                  => $this->resource->toSlug(),
            'statistic'             => $this->getStatistic(),
            'extra'                 => $this->getCustomExtra(),
            'feed_param'            => $this->getFeedParams(),
            'owner_navigation_link' => $this->resource->owner_navigation_link,
        ], $this->getSharedFeedInfos($context));
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'     => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'     => $this->resource->total_view,
            'total_share'    => $this->resource->total_share,
            'total_comment'  => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'    => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_tag'      => $this->resource->total_tag,
            'total_download' => $this->resource->total_download,
            'total_vote'     => $this->resource->total_vote,
            'total_rating'   => $this->resource->total_rating,
        ];
    }

    private function getSharedFeedInfos(User $context): array
    {
        $info  = __p('photo::phrase.added_a_photo');
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
            $info  = $owner->entityType() . '_user_name_updated_their_profile_photo';

            if ($owner instanceof \MetaFox\User\Models\User) {
                $info = __p('user::phrase.user_name_updated_their_profile_picture', [
                    'gender'     => $album->ownerEntity->possessive_gender,
                    'isAuthUser' => (int) ($context->entityId() == $owner->entityId()),
                ]);
            }
        }

        if ($this->resource->is_cover_photo) {
            $owner = $album->owner;
            $info  = $owner->entityType() . '_user_name_updated_their_cover_photo';

            if ($owner instanceof \MetaFox\User\Models\User) {
                $info = __p('user::phrase.user_name_updated_their_cover_photo', [
                    'gender'     => $album->ownerEntity->possessive_gender,
                    'isAuthUser' => (int) ($context->entityId() == $owner->entityId()),
                ]);
            }
        }

        return [
            'info'                 => $info,
            'status'               => $this->toFeedContent($this->resource),
            'location'             => $this->toLocation($this->resource),
            'total_friends_tagged' => $this->resource->total_tag_friend,
            'photo_type'           => $this->resource->photo_type,
        ];
    }
}
