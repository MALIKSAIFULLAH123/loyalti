<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use MetaFox\Music\Jobs\DeleteGenreJob;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Genre;
use MetaFox\Music\Models\GenreRelation;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;

/**
 * Class GenreRepository.
 * @property Genre $model
 * @method   Genre getModel()
 * @method   Genre find($id, $columns = ['*'])()
 * @ignore
 * @codeCoverageIgnore
 */
class GenreRepository extends AbstractCategoryRepository implements GenreRepositoryInterface
{
    public function model()
    {
        return Genre::class;
    }

    public function moveToNewCategory(Genre $category, int $newCategoryId, bool $isDelete = false): void
    {
        $totalItem = $category->total_item;
        $parent    = $category?->parentCategory;
        $this->decrementTotalItemCategories($parent, $totalItem);

        $categoryIds = $category->subCategories()->pluck('id')->toArray();
        $newCategory = $this->find($newCategoryId);
        $songIds     = $category->songs()->pluck('music_songs.id')->toArray();
        $albumsIds   = $category->albums()->pluck('music_albums.id')->toArray();

        //Move Genre
        if (!empty($songIds) && $isDelete) {
            $newCategory->songs()->sync($songIds, false);
            $newCategory->albums()->sync($albumsIds, false);
        }

        //update parent_id
        Genre::query()->where('parent_id', '=', $category->entityId())->update([
            'parent_id' => $newCategory->entityId(),
            'level'     => $newCategory->level + 1,
        ]);

        $this->deleteCategoryRelations($category);

        if (!empty($categoryIds)) {
            $this->createCategoryRelationFor($newCategory, $categoryIds);
        }

        $this->incrementTotalItemCategories($newCategory, $totalItem);
    }

    public function deleteCategory(User $context, int $id, int $newCategoryId): bool
    {
        $category = $this->find($id);

        $category->delete();

        DeleteGenreJob::dispatch($category, $newCategoryId);

        $this->clearCache();

        return true;
    }

    public function deleteAllBelongTo(Genre $genre): bool
    {
        $genre->songs()->each(function (Song $song) {
            $song->delete();
        });

        $genre->albums()->each(function (Album $album) {
            $album->delete();
        });

        $genre->subCategories()->each(function (Genre $item) {
            DeleteGenreJob::dispatch($item, 0);
        });

        $this->clearCache();

        return true;
    }

    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('music.music_song.song_default_genre');
        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Genre
    {
        $defaultCategory = Settings::get('music.music_song.song_default_genre');

        return $this->getModel()->newModelQuery()
            ->where('id', $defaultCategory)->first();
    }

    public function getRelationModel(): GenreRelation
    {
        return new GenreRelation();
    }
}
