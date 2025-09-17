<?php

namespace MetaFox\ActivityPoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateTotalPoint extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $statistics = PointStatistic::query()
            ->select('apt_statistics.id')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'apt_statistics.id')
                    ->where('importer_entries.resource_type', 'user');
            })
            ->orderBy('id')
            ->get();

        if (!$statistics->count()) {
            return;
        }

        $collections = $statistics->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingTotalPoint::dispatch($collection->pluck('id')->toArray());
        }
    }
}
