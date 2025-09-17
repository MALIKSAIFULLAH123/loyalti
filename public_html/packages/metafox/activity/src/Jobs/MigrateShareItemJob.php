<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Jobs\AbstractJob;


/**
 * stub: packages/jobs/job-queued.stub
 */
class MigrateShareItemJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shares = Share::query()
            ->with(['item'])
            ->where([
                'item_type' => Feed::ENTITY_TYPE,
            ])
            ->get();

        if (!$shares->count()) {
            return;
        }

        $shares->each(function ($share) {
            $feed = $share->item;

            if (!$feed instanceof Feed) {
                return;
            }

            /**
             * Not need to migrate because total_share on feed is also of post
             */
            if ($feed->item_type == Post::ENTITY_TYPE) {
                return;
            }

            $item = $feed->item;

            if (null === $item) {
                return;
            }

            /**
             * This is case when you share a feed which was created from creating item
             */
            if (!$item instanceof Share) {
                $share->update([
                    'item_id'   => $item->entityId(),
                    'item_type' => $item->entityType(),
                ]);

                return;
            }

            /**
             * This is case when you re-share the shared feed which cover the item inside it, so total_share will not be added to item
             * This is case we need to migrate total_share from feed plus into total_share of item
             */
            $originalShare = $feed->item;

            if (!$originalShare instanceof Share) {
                return;
            }

            $item = $originalShare->item;

            if (!$item instanceof HasTotalShare) {
                return;
            }

            $item->incrementAmount('total_share', $originalShare->total_share);

            /**
             * Update item_type and item_id to item instead of feed
             * Also update context_item_type and context_item_id because you share item from another shared feed
             */
            $share->update([
                'item_id'           => $item->entityId(),
                'item_type'         => $item->entityType(),
                'context_item_id'   => $feed->entityId(),
                'context_item_type' => $feed->entityType(),
            ]);
        });
    }
}
