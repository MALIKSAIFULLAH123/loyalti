<?php

namespace MetaFox\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateEventLocation extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $apiKey = Settings::get('core.google.google_map_api_key');

        if (empty($apiKey)) {
            return null;
        }

        $events = resolve(EventRepositoryInterface::class)->getMissingLocationEvent();

        if (!$events->count()) {
            return;
        }

        $collections = $events->chunk(50);

        foreach ($collections as $collection) {
            MigrateChunkingEventLocation::dispatch($collection->pluck('id')->toArray());
        }
    }
}
