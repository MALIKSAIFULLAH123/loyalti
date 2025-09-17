<?php

namespace MetaFox\Video\Http\Resources\v1\Video\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Video\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Video\Http\Resources\v1\Video\Traits\HasExtra as VideoHasExtra;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Support\Browse\Traits\Video\HandleContentTrait;
use MetaFox\Video\Support\Facade\Video as VideoFacade;

/**
 * Class VideoItem.
 *
 * @property Video $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class VideoItem extends JsonResource
{
    use VideoHasExtra;
    use HandleContentTrait;

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
        $text = match ($this->resource->group_id > 0) {
            true  => $this->handleContentForUpload(),
            false => $this->handleContentForLink(),
        };

        if ($text) {
            $modelContent = $this->resource->group_id > 0 ? $this->resource->group : $this->resource;
            app('events')->dispatch('core.parse_content', [$modelContent, &$text]);
        }

        $shortDescription = $text;

        if ($this->resource->group_id == 0) {
            if (is_string($text)) {
                $text = parse_output()->parseItemDescription($text);
            }
        }

        if (null !== $shortDescription) {
            $shortDescription = parse_output()->getDescription($shortDescription);
        }

        return [
            'id'                => $this->resource->entityId(),
            'album_id'          => $this->resource->album_id,
            'album'             => ResourceGate::asResource($this->resource->album, 'embed'),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->title ?: __p('video::phrase.video_label_in_admin'),
            'text'              => $text,
            'description'       => $shortDescription,
            'status_text'       => VideoFacade::getStatusTexts($this->resource),
            'is_featured'       => (bool) $this->resource->is_featured,
            'is_sponsored'      => (bool) $this->resource->is_sponsor,
            'is_sponsored_feed' => (bool) $this->resource->sponsor_in_feed,
            'is_processing'     => (bool) $this->resource->is_processing,
            'is_success'        => (bool) $this->resource->is_success,
            'is_failed'         => (bool) $this->resource->is_failed,
            'is_valid'          => (bool) $this->resource->is_valid,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'destination'       => $this->resource->destination,
            'duration'          => $this->resource->duration,
            'video_url'         => $this->resource->video_url,
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'slug'              => $this->resource->toSlug(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'categories'        => new CategoryItemCollection($this->resource->categories),
            'creation_date'     => $this->resource->created_at,
            'verified_at'       => $this->resource->verified_at,
            'extra'             => $this->getExtra(),
        ];
    }
}
