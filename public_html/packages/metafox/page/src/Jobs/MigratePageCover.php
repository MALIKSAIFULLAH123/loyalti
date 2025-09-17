<?php

namespace MetaFox\Page\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Jobs\AbstractJob;

class MigratePageCover extends AbstractJob
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
        $pages = Page::query()
            ->select('pages.id')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'pages.id')
                    ->where('importer_entries.resource_type', 'page');
            })
            ->whereNotNull('pages.cover_file_id')
            ->whereNull('pages.cover_id')
            ->orderBy('id')
            ->get();

        if (!$pages->count()) {
            return;
        }

        $collections = $pages->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingPageCover::dispatch($collection->pluck('id')->toArray());
        }
    }
}
