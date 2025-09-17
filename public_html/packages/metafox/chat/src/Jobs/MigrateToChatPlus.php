<?php

namespace MetaFox\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Chat\Repositories\RoomRepositoryInterface;

class MigrateToChatPlus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
    }

    public function handle()
    {
        $rooms = resolve(RoomRepositoryInterface::class)
            ->getModel()
            ->newQuery()
            ->cursor();

        if (!$rooms->count()) {
            return;
        }

        $collections = $rooms->chunk(10);

        foreach ($collections as $collection) {
            MigrateChunkedConversationToChatPlus::dispatch($collection->pluck('id')->toArray());
        }
    }
}
