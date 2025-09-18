<?php

namespace MetaFox\Music\Http\Resources\v1\Genre\Admin;

use MetaFox\Music\Models\Genre as Model;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractUpdateCategoryForm;

/**
 * Class UpdateGenreForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateGenreForm extends AbstractUpdateCategoryForm
{
    protected GenreRepositoryInterface $repository;

    protected function categoryRepository(): GenreRepositoryInterface
    {
        return resolve(GenreRepositoryInterface::class);
    }

    protected function getActionUrl(): string
    {
        return url_utility()->makeApiUrl('admincp/music/genre/' . $this->resource->id);
    }
}
