<?php

namespace MetaFox\Photo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateApprovedPhotoGroupItemJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param int $page
     * @param int $limit
     */
    public function __construct(protected int $page = 1, protected int $limit = 500, protected bool $refreshBeforeStarting = false)
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->refreshBeforeStarting) {
            $this->refreshData();
        }

        $items = PhotoGroupItem::query()
            ->with(['detail'])
            ->simplePaginate($this->limit, ['photo_group_items.*'], 'page', $this->page);

        if (!$items->count()) {
            return;
        }

        $items = $items->items();

        if (!is_array($items) || !count($items)) {
            return;
        }

        $pendingIds = collect($items)
            ->filter(function (PhotoGroupItem $item) {
                if (null === $item->detail) {
                    return false;
                }

                if (!$item->detail->isApproved()) {
                    return true;
                }

                return false;
            })
            ->pluck('id')
            ->toArray();

        if (count($pendingIds)) {
            PhotoGroupItem::query()
                ->whereIn('id', $pendingIds)
                ->update(['is_approved' => 0]);
        }

        self::dispatch($this->page + 1, $this->limit, false);
    }

    protected function refreshData(): void
    {
        PhotoGroupItem::query()
            ->update(['is_approved' => 1]);
    }
}
