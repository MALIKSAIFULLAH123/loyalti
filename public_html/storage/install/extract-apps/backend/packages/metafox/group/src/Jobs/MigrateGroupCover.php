<?php

namespace MetaFox\Group\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateGroupCover extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        if (!DB::table('photo_albums')->exists()) {
            return;
        }
        $groups = Group::query()
            ->select('groups.id')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'groups.id')
                    ->where('importer_entries.resource_type', 'group');
            })
            ->whereNotNull('groups.cover_file_id')
            ->whereNull('groups.cover_id')
            ->orderBy('id')
            ->lazy();

        if (!$groups->count()) {
            return;
        }

        $collections = $groups->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingGroupCover::dispatch($collection->pluck('id')->toArray());
        }
    }
}
