<?php

namespace MetaFox\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Notification\Models\Notification;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateNotificationData extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $notifications = Notification::query()
            ->whereNull('data')
            ->orWhere('data', 'like', '%"type":"metafox_migration"%')
            ->orderBy('id')
            ->lazy();

        if (!$notifications->count()) {
            return;
        }

        $collections = $notifications->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingNotificationData::dispatch($collection->pluck('id')->toArray());
        }
    }
}
