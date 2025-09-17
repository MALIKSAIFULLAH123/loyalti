<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingActivityLike extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $data = [])
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if (!count($this->data)) {
            return;
        }
        $id     = array_keys($this->data);
        $feedId = array_values($this->data);

        $likes = Like::query()
            ->whereIn('item_id', $id)
            ->where('item_type', 'forum_thread')
            ->get();

        if ($likes->count()) {
            Like::query()
                ->whereIn('item_id', $feedId)
                ->where('item_type', 'feed')
                ->delete();
            foreach ($likes as $like) {
                $newLike            = $like->replicate();
                $newLike->item_id   = $this->data[$like->item_id];
                $newLike->item_type = 'feed';
                $newLike->saveQuietly();
            }
        }

        $likeAggs = LikeAgg::query()
            ->whereIn('item_id', $id)
            ->where('item_type', 'forum_thread')
            ->get();

        if ($likeAggs->count()) {
            LikeAgg::query()
                ->whereIn('item_id', $feedId)
                ->where('item_type', 'feed')
                ->delete();
            foreach ($likeAggs as $likeAgg) {
                $newLikeAgg            = $likeAgg->replicate();
                $newLikeAgg->item_id   = $this->data[$likeAgg->item_id];
                $newLikeAgg->item_type = 'feed';
                $newLikeAgg->saveQuietly();
            }
        }
    }
}
