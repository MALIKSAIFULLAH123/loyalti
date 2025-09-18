<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Category\Admin;

use MetaFox\Form\Html\AbstractDestroyCategoryForm;
use MetaFox\Marketplace\Models\Category as Model;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;

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
        return '/admincp/marketplace/category/' . $this->resource->id;
    }

    /**
     * @return string
     */
    protected function getPluralizationItemType(): string
    {
        return __p('marketplaces');
    }
}
