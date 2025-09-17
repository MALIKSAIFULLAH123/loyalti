<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Music\Models\PlaylistData;
use MetaFox\Music\Models\Song;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $model = new \MetaFox\Music\Models\Playlist();

        // to do here
        $this->addTotalDuration($model);
        $this->addTotalTrack($model);
    }

    public function addTotalDuration(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_length';

        //        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
        //            return;
        //        }

        $query = PlaylistData::query()
            ->selectRaw('playlist_id, sum(duration) as aggregate')
            ->join('music_songs', 'music_songs.id', '=', 'music_playlist_data.item_id')
            ->groupBy('playlist_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'playlist_id', false);
    }

    public function addTotalTrack(Illuminate\Database\Eloquent\Model $model)
    {
        $updateColumn = 'total_track';

        //        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
        //            return;
        //        }

        $query = PlaylistData::query()
            ->selectRaw('playlist_id, count(*) as aggregate')
            ->join('music_songs', 'music_songs.id', '=', 'music_playlist_data.item_id')
            ->groupBy('playlist_id');

        DbTableHelper::migrateCounter($model, $updateColumn, $query, 'id', 'playlist_id', false);
    }
};
