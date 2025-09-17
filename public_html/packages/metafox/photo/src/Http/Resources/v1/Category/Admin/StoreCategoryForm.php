<?php

namespace MetaFox\Photo\Http\Resources\v1\Category\Admin;

use MetaFox\Photo\Models\Category as Model;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractStoreCategoryForm;

/**
 * Class StoreCategoryForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreCategoryForm extends AbstractStoreCategoryForm
{
    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    protected function getActionUrl(): string
    {
        return apiUrl('admin.photo.category.store');
    }
}
