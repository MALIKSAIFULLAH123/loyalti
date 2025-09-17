<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Facades\Photo as PhotoFacade;
use MetaFox\Photo\Support\Traits\PhotoHasExtra;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class PhotoEmbed.
 * @property Photo $resource
 */
class PhotoEmbed extends JsonResource
{
    use PhotoHasExtra;

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

        return [
            'id'               => $this->resource->entityId(),
            'module_name'      => Photo::ENTITY_TYPE,
            'resource_name'    => $this->resource->entityType(),
            'width'            => $fileItem?->width,
            'height'           => $fileItem?->height,
            'user'             => ResourceGate::user($this->resource->userEntity),
            'owner'            => ResourceGate::user($this->resource->ownerEntity),
            'mature'           => $this->resource->mature,
            'image'            => PhotoFacade::getImagesWithContext($context, $this->resource),
            'avatar'           => $this->when($isLatestAvatar, $this->resource->ownerEntity?->avatars),
            'is_featured'      => $this->resource->is_featured,
            'is_sponsor'       => $this->resource->is_sponsor,
            'is_profile_photo' => $this->resource->is_profile_photo,
            'text'             => $this->getPhotoContent($this->resource),
            'slug'             => $this->resource->toSlug(),
            'tagged_friends'   => new PhotoTaggedFriendCollection($taggedFriends),
            'extra'            => $this->getCustomExtra(),
        ];
    }

    /**
     * @param  Photo  $resource
     * @return string
     * @todo Need refactor this to a accessor for reuse
     */
    protected function getPhotoContent(Photo $resource): string
    {
        $reactItem = $resource->reactItem();

        $photoText = MetaFoxConstant::EMPTY_STRING;

        if ($reactItem instanceof PhotoGroup) {
            $photoText = $reactItem?->content;
        }

        if (null !== $resource->photoInfo) {
            $photoText = $resource->photoInfo->text_parsed;
        }

        return ban_word()->clean($photoText ?? '');
    }
}
