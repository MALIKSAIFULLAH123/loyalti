<?php

namespace MetaFox\Importer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateLikeComment extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $photoGroups = PhotoGroup::query()
            ->select('photo_groups.*')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'photo_groups.id')
                    ->where('importer_entries.resource_type', 'photo_set');
            })
            ->orderBy('id')
            ->lazy();

        if (!$photoGroups->count()) {
            return;
        }

        $collections = $photoGroups->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingLikeComment::dispatch($collection->pluck('id')->toArray());
        }
    }
}
