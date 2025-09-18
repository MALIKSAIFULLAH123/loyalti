<?php

namespace MetaFox\Music\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\GenreData;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingAlbumGenre extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $albumIds = [])
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if (!count($this->albumIds)) {
            return;
        }

        $albums = Album::query()
            ->whereIn('id', $this->albumIds)
            ->get();

        if (!$albums->count()) {
            return;
        }

        $data = [];

        foreach ($albums as $album) {
            $songIds = $album->songs()->get()->pluck('id')->toArray() ?? null;
            if (empty($songIds)) {
                continue;
            }

            $genresData = GenreData::query()
                ->whereIn('item_id', $songIds)
                ->distinct()
                ->get('genre_id');

            if (!$genresData->count()) {
                continue;
            }

            $this->prepareDataInsert($data, $album, $genresData);
        }

        $this->insertData($data);
    }

    private function insertData(array $genresData): void
    {
        GenreData::query()
            ->select('music_genre_data.*')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'music_genre_data.id')
                    ->where('importer_entries.resource_type', 'music_genre_data');
            })
            ->where('item_type', 'music_album')
            ->delete();

        $chunks = array_chunk($genresData, 100);

        foreach ($chunks as $chunk) {
            GenreData::query()->insert($chunk);
        }
    }

    private function prepareDataInsert(array &$data, Album $album, Collection $genresData): void
    {
        foreach ($genresData as $genreData) {
            $item = [
                'genre_id'  => $genreData->genre_id,
                'item_id'   => $album->entityId(),
                'item_type' => $album->entityType(),
            ];

            $key = $this->getGenreDataKey($item);

            if (!isset($data[$key])) {
                $data[$key] = $item;
            }
        }
    }

    private function getGenreDataKey(array $item): string
    {
        return $item['genre_id'] . '.' . $item['item_id'] . '.' . $item['item_type'];
    }
}
