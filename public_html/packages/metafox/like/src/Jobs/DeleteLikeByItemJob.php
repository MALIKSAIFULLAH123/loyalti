<?php

namespace MetaFox\Like\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Platform\Jobs\AbstractJob;


/**
 * stub: packages/jobs/job-queued.stub
 */
class DeleteLikeByItemJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int    $itemId;
    protected string $itemType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $itemId, string $itemType)
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
    public function handle(): void
    {
        $likesAgg = LikeAgg::query()
            ->where('item_id', $this->itemId)
            ->where('item_type', $this->itemType)
            ->get();

        $likes = Like::query()
            ->where('item_id', $this->itemId)
            ->where('item_type', $this->itemType)
            ->get();

        if (!empty($likesAgg) && !empty($likes)) {
            foreach ($likes as $like) {
                $like->delete();
            }
            foreach ($likesAgg as $likeAgg) {
                $likeAgg->delete();
            }
        }
    }
}
