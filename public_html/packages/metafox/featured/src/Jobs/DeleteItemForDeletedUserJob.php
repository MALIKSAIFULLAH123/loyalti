<?php

namespace MetaFox\Featured\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Featured\Models\Item;
use MetaFox\Platform\Jobs\AbstractJob;

class DeleteItemForDeletedUserJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $userId, protected int $page = 1, protected int $limit = 50)
    {
        parent::__construct();
    }

    public function handle()
    {
        $items = Item::query()
            ->with(['item'])
            ->where('user_id', $this->userId)
            ->simplePaginate($this->limit, ['featured_items.*'], 'page', $this->page);

        if (!$items->count()) {
            return;
        }

        collect($items->items())
            ->each(function (Item $item) {
                $item->delete();
            });

        self::dispatch($this->userId, $this->page + 1, $this->limit);
    }
}
