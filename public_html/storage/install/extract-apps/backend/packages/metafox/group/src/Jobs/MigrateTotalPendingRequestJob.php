<?php

namespace MetaFox\Group\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateTotalPendingRequestJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        if (DB::getDefaultConnection() === 'pgsql') {
            DB::statement('UPDATE groups
            SET total_pending_request = (
                SELECT COUNT(*)
                FROM group_requests
                WHERE group_requests.group_id = groups.id AND group_requests.status_id = 0
                )');
        }

        if (DB::getDefaultConnection() === 'mysql') {
            DB::statement('UPDATE `groups`
            INNER JOIN (
                SELECT group_id, COUNT(*) as pending_request
                FROM group_requests
                WHERE group_requests.status_id = 0
                GROUP BY group_id
            ) as k
            ON k.group_id = groups.id
            SET total_pending_request = k.pending_request');
        }
    }
}
