<?php

namespace MetaFox\Blog\Http\Resources\v1\Category\Admin;

use MetaFox\Blog\Models\Category as Model;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\Html\AbstractDestroyCategoryForm;

/**
 * Class DestroyCategoryForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DestroyCategoryForm extends AbstractDestroyCategoryForm
{
    public function boot(CategoryRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource = $repository->find($id);
    }

    /**
     * @return string
     */
    protected function getActionUrl(): string
    {
        return '/admincp/blog/category/' . $this->resource->id;
    }

    /**
     * @return string
     */
    protected function getPluralizationItemType(): string
    {
        return __p('blogs');
    }
}
