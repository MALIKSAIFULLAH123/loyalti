<?php

namespace MetaFox\Photo\Http\Resources\v1\Album\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Support\Traits\Album\ExtraTrait;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class AlbumItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AlbumItem extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $description = '';

        $albumText = $this->resource->albumText;

        if ($albumText) {
            $description = $albumText->text_parsed;
        }

        return [
            'id'                => $this->resource->id,
            'module_name'       => 'photo',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'description'       => parse_output()->getDescription($description),
            'total_item'        => $this->resource->total_item,
            'album_type'        => $this->resource->album_type,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'is_featured'       => (bool) $this->resource->is_featured,
            'is_sponsored'      => (bool) $this->resource->is_sponsor,
            'sponsor_in_feed'   => (bool) $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getExtra(),
        ];
    }
}
