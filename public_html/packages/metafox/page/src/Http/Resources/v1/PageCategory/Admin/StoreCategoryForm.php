<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory\Admin;

use MetaFox\Page\Models\Category as Model;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractStoreCategoryForm;

/**
 * Class StoreCategoryForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreCategoryForm extends AbstractStoreCategoryForm
{
    protected function categoryRepository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }

    protected function getActionUrl(): string
    {
        return url_utility()->makeApiUrl('admincp/page/category');
    }
}
