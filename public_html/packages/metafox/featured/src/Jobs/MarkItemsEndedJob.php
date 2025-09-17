<?php

namespace MetaFox\Featured\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\Jobs\AbstractJob;

class MarkItemsEndedJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $limit = 50)
    {
        parent::__construct();
    }

    public function handle()
    {
        Item::query()
            ->where('status', '=', Constants::FEATURED_ITEM_STATUS_RUNNING)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', Carbon::now())
            ->limit($this->limit)
            ->get()
            ->each(function (Item $item) {
                Feature::markFeaturedItemEnded($item);
            });
    }
}
