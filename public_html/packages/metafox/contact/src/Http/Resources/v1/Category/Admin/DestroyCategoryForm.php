<?php

namespace MetaFox\Contact\Http\Resources\v1\Category\Admin;

use MetaFox\Contact\Models\Category as Model;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
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
        $this->resource   = $repository->find($id);
    }

    /**
     * @return string
     */
    protected function getActionUrl(): string
    {
        return '/admincp/contact/category/' . $this->resource->id;
    }

    /**
     * @return string
     */
    protected function getPluralizationItemType(): string
    {
        return __p('contact');
    }
}
