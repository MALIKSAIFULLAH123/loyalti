<?php

namespace MetaFox\Music\Http\Resources\v1\Genre\Admin;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Music\Models\Genre as Model;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\AbstractStoreCategoryForm;

/**
 * Class StoreGenreForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreGenreForm extends AbstractStoreCategoryForm
{

    protected function categoryRepository(): GenreRepositoryInterface
    {
        return resolve(GenreRepositoryInterface::class);
    }

    protected function getActionUrl(): string
    {
        return url_utility()->makeApiUrl('admincp/music/genre');
    }

    /**
     * @param Section $section
     * @return void
     */
    protected function getParentField(Section $section): void
    {
        $section->addField(
            Builder::choice('parent_id')
                ->label(__p('music::phrase.parent_genre'))
                ->required(false)
                ->options($this->getParentCategoryOptions())
        );
    }
}
