<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingPostId extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $threadIds = [])
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if (!count($this->threadIds)) {
            return;
        }

        $threads = ForumThread::query()
            ->whereIn('id', $this->threadIds)
            ->get();

        if (!$threads->count()) {
            return;
        }

        foreach ($threads as $thread) {
            $update = [
                'first_post_id' => 0,
                'last_post_id'  => 0,
            ];

            if ($thread->firstPost instanceof ForumPost) {
                Arr::set($update, 'first_post_id', $thread->firstPost->entityId());
            }

            if ($thread->lastPost instanceof ForumPost) {
                Arr::set($update, 'last_post_id', $thread->lastPost->entityId());
            }

            $thread->timestamps = false;

            $thread->updateQuietly($update);
        }
    }
}
