<?php

namespace MetaFox\Search\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Search\Models\HashtagStatistic;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class MigrateHashtagStatisticJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tags = DB::table('search_tag_data')
            ->select(['tag_id', DB::raw('COUNT(*) AS total_item')])
            ->groupBy('tag_id')
            ->orderBy('tag_id')
            ->get();

        if (!$tags->count()) {
            return;
        }

        $maps = $tags->map(function ($tag) {
            return [
                'tag_id'     => $tag->tag_id,
                'total_item' => $tag->total_item,
            ];
        })->toArray();

        HashtagStatistic::query()->upsert($maps, ['tag_id'], ['total_item']);
    }
}
