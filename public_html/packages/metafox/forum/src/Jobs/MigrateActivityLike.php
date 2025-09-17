<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateActivityLike extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $feeds = Feed::query()
            ->select(['activity_feeds.item_id', 'activity_feeds.id'])
            ->join('importer_entries', function (JoinClause $join) {
                $join->on('importer_entries.resource_id', '=', 'activity_feeds.id')
                    ->where('importer_entries.resource_type', '=', 'activity_feed');
            })
            ->where([
                'activity_feeds.type_id' => 'forum_thread',
            ])
            ->orderBy('activity_feeds.id')
            ->lazy();

        if (!$feeds->count()) {
            return;
        }

        $collections = $feeds->chunk(50);

        foreach ($collections as $collection) {
            MigrateChunkingActivityLike::dispatch($collection->pluck('id', 'item_id')->toArray());
        }
    }
}
