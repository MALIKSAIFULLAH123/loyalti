<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Category\Admin;

use Foxexpert\Sevent\Models\Category as Model;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;

/**
 * Class UpdateCategoryForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCategoryForm extends StoreCategoryForm
{
    protected CategoryRepositoryInterface $repository;

    public function boot(CategoryRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $this->repository->find($id);
    }

    protected function prepare(): void
    {
        $model = $this->resource;

        $this->asPut()->title(__p('core::phrase.edit_category'))
            ->action(url_utility()->makeApiUrl('admincp/sevent/category/' . $this->resource->id))
            ->setValue([
                'name'      => Language::getPhraseValues($model->name_var),
                'is_active' => $model->is_active,
                'ordering'  => $model->ordering,
                'parent_id' => $model->parent_id,
                'name_url'  => $model->name_url,
            ]);
    }

    protected function isDisabled(): bool
    {
        $defaultCategoryParentIds   = $this->repository->getDefaultCategoryParentIds();

        $isDisabled = $this->resource->is_default;

        if (in_array($this->resource->entityId(), $defaultCategoryParentIds)) {
            $isDisabled = true;
        }

        return $isDisabled;
    }
}
