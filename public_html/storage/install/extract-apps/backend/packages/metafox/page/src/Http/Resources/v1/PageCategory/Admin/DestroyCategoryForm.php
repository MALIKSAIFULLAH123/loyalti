<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory\Admin;

use MetaFox\Form\Html\AbstractDestroyCategoryForm;
use MetaFox\Page\Models\Category as Model;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;

/**
 * Class DestroyCategoryForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DestroyCategoryForm extends AbstractDestroyCategoryForm
{
    public function __construct($resource, PageCategoryRepositoryInterface $repository)
    {
        parent::__construct($resource);
        $this->repository = $repository;
    }

    public function boot(PageCategoryRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $repository->find($id);
    }

    /**
     * @return string
     */
    protected function getActionUrl(): string
    {
        return '/admincp/page/category/' . $this->resource->id;
    }

    /**
     * @return string
     */
    protected function getPluralizationItemType(): string
    {
        return __p('pages');
    }
}
