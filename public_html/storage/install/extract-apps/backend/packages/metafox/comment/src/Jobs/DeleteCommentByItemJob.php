<?php

namespace MetaFox\Comment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class DeleteCommentByItemJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int    $itemId;
    protected string $itemType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($itemId, $itemType)
    {
        parent::__construct();
        $this->itemId   = $itemId;
        $this->itemType = $itemType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $comments = Comment::query()
            ->with(['item'])
            ->where('item_type', '=', $this->itemType)
            ->where('item_id', '=', $this->itemId)
            ->get();

        foreach ($comments as $comment) {
            try {
                $comment->delete();
            } catch (\Throwable $exception) {
            }
        }
    }
}
