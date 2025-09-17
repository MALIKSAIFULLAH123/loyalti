<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Support\Traits\PhotoHasExtra;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PhotoItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PhotoItem extends JsonResource
{
    use PhotoHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        $context   = user();
        $content   = null;
        $reactItem = $this->resource->reactItem();

        if (null !== $this->resource->content) {
            $content = $this->resource->content;
        }

        if ($this->resource->group_id > 0 && null === $content) {
            $content = $reactItem->content;
        }

        if (null !== $content) {
            app('events')->dispatch('core.parse_content', [$this->resource, &$content]);

            $content = parse_output()->getDescription($content);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'description'       => $content,
            'type_id'           => $this->resource->type_id,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'is_sponsored'      => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'is_cover'          => $this->resource->is_cover,
            'is_profile_photo'  => $this->resource->is_profile_photo,
            'is_cover_photo'    => $this->resource->is_cover_photo,
            'is_approved'       => !$this->resource->is_approved,
            'mature'            => $this->resource->mature,
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'slug'              => $this->resource->toSlug(),
            'extra'             => $this->getCustomExtra(),
        ];
    }
}
