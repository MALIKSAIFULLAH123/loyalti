<?php

namespace MetaFox\Group\Http\Resources\v1\Category\Admin;

use MetaFox\Group\Models\Category as Model;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractUpdateCategoryForm;

/**
 * Class UpdateCategoryForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCategoryForm extends AbstractUpdateCategoryForm
{
    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    protected function getActionUrl(): string
    {
        return url_utility()->makeApiUrl('admincp/group/category/' . $this->resource->id);
    }
}
