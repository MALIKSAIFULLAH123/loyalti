<?php

namespace MetaFox\Music\Repositories\Eloquent;

use MetaFox\Music\Models\GenreData;
use MetaFox\Music\Repositories\GenreDataRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class GenreDataRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class GenreDataRepository extends AbstractRepository implements GenreDataRepositoryInterface
{
    public function model()
    {
        return GenreData::class;
    }

    public function updateData(Content $content, ?array $genreIds = []): void
    {
        if (null === $genreIds) {
            return;
        }

        $pivot = $content->genres();

        $current = $pivot->newPivotQuery()
            ->pluck('genre_id')
            ->toArray();

        $attach = array_diff($genreIds, $current);
        $detach = array_diff($current, $genreIds);

        if (count($attach)) {
            $pivot->attach($attach, ['item_type' => $content->entityType()]);
        }

        if (count($detach)) {
            $this->deleteData($content, $detach);
        }
    }

    public function deleteData(Content $content, array $genreIds = []): void
    {
        $pivot = $content->genres();

        if (count($genreIds)) {
            $pivot->wherePivotIn('music_genre_data.genre_id', $genreIds);
        }

        $pivot->newPivotQuery()
            ->get()
            ->each(function ($item) use ($pivot) {
                $pivot->newPivot(json_decode(json_encode($item), true), true)->delete();
            });
    }
}
