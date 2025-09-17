<?php

namespace MetaFox\Video\Http\Resources\v1\Category\Admin;

use MetaFox\Form\Html\AbstractDestroyCategoryForm;
use MetaFox\Video\Models\Category as Model;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

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
        return 'admincp/video/category/' . $this->resource->id;
    }

    /**
     * @return string
     */
    protected function getPluralizationItemType(): string
    {
        return __p('videos');
    }
}
