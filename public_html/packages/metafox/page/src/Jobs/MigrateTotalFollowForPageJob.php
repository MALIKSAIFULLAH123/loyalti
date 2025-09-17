<?php

namespace MetaFox\Page\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateTotalFollowForPageJob extends AbstractJob implements ShouldBeUnique
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
    public function handle(): void
    {
        if (!Schema::hasColumns('pages', ['total_follower', 'total_following'])) {
            return;
        }

        if (!Schema::hasTable('activity_subscriptions')) {
            return;
        }

        try {
            match (database_driver()) {
                'pgsql' => $this->updateValuesByPostgres(),
                default => $this->updateValuesByMySql(),
            };
        } catch (\Exception $e) {
            Log::channel('dev')->info($e->getMessage());
        }
    }

    private function updateValuesByPostgres(): void
    {
        DB::statement("
            UPDATE pages
            SET total_follower = (
                SELECT COUNT(*)
                FROM activity_subscriptions
                join user_entities as us on activity_subscriptions.user_id = us.id
                WHERE activity_subscriptions.owner_id = pages.id
                  AND activity_subscriptions.special_type is null
                  AND activity_subscriptions.is_active = true
                  AND activity_subscriptions.user_id != pages.id
                  AND us.entity_type != 'group'
                  AND us.id is not null
                )");

        DB::statement("
            UPDATE pages
            SET total_following = (
                SELECT COUNT(*)
                FROM activity_subscriptions
                join user_entities as us on activity_subscriptions.owner_id = us.id
                WHERE activity_subscriptions.user_id = pages.id
                  AND activity_subscriptions.special_type is null
                  AND activity_subscriptions.is_active = true
                  AND activity_subscriptions.owner_id != pages.id
                  AND us.entity_type != 'group'
                  AND us.id is not null
                )");
    }

    private function updateValuesByMysql(): void
    {
        DB::statement('UPDATE `pages`
            INNER JOIN (
                SELECT owner_id, COUNT(*) as total_follower
                FROM activity_subscriptions
                join user_entities as us on activity_subscriptions.user_id = us.id
                WHERE activity_subscriptions.special_type is null
                  AND activity_subscriptions.is_active = true
                  AND us.entity_type != "group"
                  AND us.id is not null
                GROUP BY owner_id
            ) as k
            ON k.owner_id = pages.id
            SET `pages`.total_follower = k.total_follower - 1');

        DB::statement('UPDATE `pages`
            INNER JOIN (
                SELECT user_id, COUNT(*) as total_following
                FROM activity_subscriptions
                join user_entities as us on activity_subscriptions.owner_id = us.id
                WHERE activity_subscriptions.special_type is null
                  AND activity_subscriptions.is_active = true
                  AND us.entity_type != "group"
                  AND us.id is not null
                GROUP BY user_id
            ) as k
            ON k.user_id = pages.id
            SET `pages`.total_following = k.total_following - 1');

    }
}
