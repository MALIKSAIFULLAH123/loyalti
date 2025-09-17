<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateStatistic extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private bool $migrateLevel = false)
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->migrateLevel) {
            resolve(ForumRepositoryInterface::class)->migrateForumLevel();
        }

        Forum::query()
            ->withTrashed()
            ->update([
                'total_thread'  => 0,
                'total_comment' => 0,
                'total_sub'     => 0,
            ]);

        $smallestLevel = Forum::query()
            ->withTrashed()
            ->orderByDesc('level')
            ->first();

        if (null === $smallestLevel) {
            return;
        }

        resolve(ForumRepositoryInterface::class)->migrateStatistics($smallestLevel->level);
    }
}
