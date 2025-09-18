<?php

namespace MetaFox\Music\Http\Resources\v1\Genre\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Music\Models\Genre;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Music\Support\Browse\Traits\Genre\ExtraTrait;
use MetaFox\Music\Support\Browse\Traits\Genre\StatisticTrait;

/**
 * Class GenreItem.
 * @property Genre $resource
 */
class GenreItem extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $parent = null;

        if ($this->resource->parent_id) {
            $parent = new GenreEmbed($this->resource->parentCategory);
        }

        $statistics = $this->getStatistic();
        $totalItem  = Arr::get($statistics, 'total_item', 0);
        $totalSub   = Arr::get($statistics, 'total_sub', 0);

        return [
            'id'             => $this->resource->entityId(),
            'is_active'      => $this->isActive(),
            'module_name'    => 'music',
            'resource_name'  => $this->resource->entityType(),
            'name'           => $this->resource->name,
            'name_url'       => $this->resource->name_url,
            'ordering'       => $this->resource->ordering,
            'url'            => $totalItem ? $this->resource->toUrl() : null,
            'subs'           => new GenreItemCollection($this->resource->subCategories),
            'parent'         => $parent,
            'is_default'     => $this->resource->is_default,
            'extra'          => $this->getExtra(),
            'statistic'      => $statistics,
            'total_sub_link' => $totalSub ? $this->resource->toAdmincpSubLink() : null,
        ];
    }

    public function isActive(): ?int
    {
        /** @var GenreRepositoryInterface $categoryRepository */
        $categoryRepository         = resolve(GenreRepositoryInterface::class);
        $defaultCategoryParentIds   = $categoryRepository->getDefaultCategoryParentIds();

        $isActive = !$this->resource->is_default ? $this->resource->is_active : null;

        if (in_array($this->resource->entityId(), $defaultCategoryParentIds)) {
            $isActive = null;
        }

        return $isActive;
    }
}
