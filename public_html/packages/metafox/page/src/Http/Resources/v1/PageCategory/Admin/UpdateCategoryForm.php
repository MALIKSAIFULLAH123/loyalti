<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory\Admin;

use MetaFox\Page\Models\Category as Model;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractUpdateCategoryForm;

/**
 * Class UpdateCategoryForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCategoryForm extends AbstractUpdateCategoryForm
{
    protected function categoryRepository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }

    protected function getActionUrl(): string
    {
        return url_utility()->makeApiUrl('admincp/page/category/' . $this->resource->id);
    }
}
