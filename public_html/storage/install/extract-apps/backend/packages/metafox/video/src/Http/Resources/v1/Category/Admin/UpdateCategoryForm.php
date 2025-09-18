<?php

namespace MetaFox\Video\Http\Resources\v1\Category\Admin;

use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractUpdateCategoryForm;
use MetaFox\Video\Models\Category as Model;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

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
        return url_utility()->makeApiUrl('admincp/video/category/' . $this->resource->id);
    }
}
