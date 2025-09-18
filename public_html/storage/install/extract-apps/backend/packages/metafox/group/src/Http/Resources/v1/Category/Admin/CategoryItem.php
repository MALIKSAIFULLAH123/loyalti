<?php

namespace MetaFox\Group\Http\Resources\v1\Category\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;

/**
 * Class CategoryItem.
 * @property Category $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryItem extends JsonResource
{
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
            $parent = new CategoryEmbed($this->resource->parentCategory);
        }

        return [
            'id'             => $this->resource->entityId(),
            'is_active'      => $this->isActive(),
            'is_default'     => $this->resource->is_default,
            'module_name'    => 'group',
            'resource_name'  => $this->resource->entityType(),
            'name'           => $this->resource->name,
            'total_sub'      => $this->resource->subCategories->count(),
            'total_sub_link' => $this->toSubLink(),
            'total_item'     => $this->resource->total_item,
            'ordering'       => $this->resource->ordering,
            'url'            => $this->resource->toUrl(),
            'subs'           => new CategoryItemCollection($this->resource->subCategories),
            'parent'         => $parent,
        ];
    }

    protected function toSubLink(): ?string
    {
        if (!$this->resource->subCategories->count()) {
            return null;
        }

        return $this->resource->toSubCategoriesLink();
    }

    public function isActive(): ?int
    {
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository       = resolve(CategoryRepositoryInterface::class);
        $defaultCategoryParentIds = $categoryRepository->getDefaultCategoryParentIds();

        $isActive = !$this->resource->is_default ? $this->resource->is_active : null;

        if (in_array($this->resource->entityId(), $defaultCategoryParentIds)) {
            $isActive = null;
        }

        return $isActive;
    }
}
