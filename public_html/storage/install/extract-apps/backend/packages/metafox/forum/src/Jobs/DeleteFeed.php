<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Platform\Jobs\AbstractJob;

class DeleteFeed extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ForumPost $post)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->post->activity_feed->delete();
    }
}
