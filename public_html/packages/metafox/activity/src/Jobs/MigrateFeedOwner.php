<?php

namespace MetaFox\Activity\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateFeedOwner extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $types = [
            'video'         => 'videos',
            'activity_post' => 'activity_posts',
            'photo_set'     => 'photo_groups',
            'link'          => 'core_links',
        ];

        foreach ($types as $type => $table) {
            try {
                DB::statement("UPDATE activity_streams AS a
                    SET owner_id = i.owner_id, owner_type = i.owner_type
                    FROM $table AS i
                    WHERE a.item_id = i.id AND a.item_type = '$type' AND a.owner_type != i.owner_type;");
                DB::statement("UPDATE activity_feeds AS a
                    SET owner_id = i.owner_id, owner_type = i.owner_type
                    FROM $table AS i
                    WHERE a.item_id = i.id AND a.item_type = '$type' AND a.owner_type != i.owner_type;");
            } catch (Exception $e) {
                Log::error(sprintf('%s:%s', __METHOD__, $e->getMessage()));
            }
        }
    }
}
