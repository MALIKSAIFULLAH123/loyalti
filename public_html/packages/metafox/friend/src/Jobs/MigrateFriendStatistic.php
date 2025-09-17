<?php

namespace MetaFox\Friend\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;

class MigrateFriendStatistic extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $userProfiles = User::query()
            ->select('users.id')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'users.id')
                    ->where('importer_entries.resource_type', 'user');
            })
            ->join('friends', function (JoinClause $joinClause) {
                $joinClause->on('users.id', '=', 'friends.owner_id')
                    ->where('friends.owner_type', '=', 'user');
            })
            ->where('users.total_friend', '=', 0)
            ->groupBy('users.id')
            ->lazy();

        $collections = $userProfiles->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingFriendStatistic::dispatch($collection->pluck('id')->toArray());
        }
    }
}
