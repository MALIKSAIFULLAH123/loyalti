<?php

namespace MetaFox\Video\Http\Resources\v1\Category\Admin;

use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractStoreCategoryForm;
use MetaFox\Video\Models\Category as Model;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

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
        return url_utility()->makeApiUrl('admincp/video/category');
    }
}
