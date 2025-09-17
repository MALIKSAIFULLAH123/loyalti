<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Support\Support;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/**
 * Class BlogItem.
 * @property Blog $resource
 */
class BlogItem extends JsonResource
{
    use HasExtra;

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
        $shortDescription = '';
        if ($this->resource->blogText) {
            $shortDescription = parse_output()->getDescription($this->resource->blogText->text_parsed);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->title,
            'description'       => $shortDescription,
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'is_sponsored'      => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'status_text'       => Support::getStatusTexts($this->resource),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'extra'             => $this->getExtra(),
        ];
    }
}
