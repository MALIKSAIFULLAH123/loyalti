<?php

namespace MetaFox\Music\Http\Resources\v1\Genre;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Music\Models\Genre;
use MetaFox\Music\Repositories\GenreRepositoryInterface;

/**
 * Class GenreEmbed.
 *
 * @property Genre $resource
 */
class GenreEmbed extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @TODO If this app requires a Core version lower than v5.14 and the current Core version more than v5.14 remove
     *        this method and extend class MetaFox\Platform\Http\Resources\v1\Category\AbstractCategoryEmbed.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'music',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'name_url'      => $this->resource->name_url,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'is_active'     => $this->repository()->isActive($this->resource),
        ];
    }

    /**
     * @return GenreRepositoryInterface
     */
    public function repository(): GenreRepositoryInterface
    {
        return resolve(GenreRepositoryInterface::class);
    }
}
