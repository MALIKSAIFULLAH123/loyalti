<?php

namespace MetaFox\Music\Http\Resources\v1\Genre\Admin;

use MetaFox\Music\Models\Genre as Model;
use MetaFox\Form\Html\AbstractDestroyCategoryForm;
use MetaFox\Music\Repositories\GenreRepositoryInterface;

/**
 * Class DestroyGenreForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DestroyGenreForm extends AbstractDestroyCategoryForm
{
    public function boot(GenreRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('music::phrase.delete_genre'))
            ->action($this->getActionUrl())
            ->asDelete()
            ->setValue([
                'migrate_items' => 0,
            ]);
    }

    /**
     * @return string
     */
    protected function getActionUrl(): string
    {
        return '/admincp/music/genre/' . $this->resource->id;
    }

    protected function deleteConfirm(): string
    {
        return __p('music::phrase.delete_genre_confirm', ['name' => $this->resource->name]);
    }

    protected function deleteCategoryOptionLabel(): string
    {
        return __p('music::phrase.delete_genre_option_label', [
            'type' => $this->getPluralizationItemType(),
        ]);
    }

    protected function deleteAllItemOptionLabel(): string
    {
        return __p('music::phrase.delete_genre_option_delete_all_items', [
            'type' => $this->getPluralizationItemType(),
        ]);
    }

    protected function moveAllItemOptionLabel(): string
    {
        return __p('music::phrase.delete_genre_option_move_all_items', [
            'type' => $this->getPluralizationItemType(),
        ]);
    }

    protected function categoryOptionLabel(): string
    {
        return __p('music::phrase.genre');
    }

    /**
     * @return string
     */
    protected function getPluralizationItemType(): string
    {
        return __p('music');
    }
}
