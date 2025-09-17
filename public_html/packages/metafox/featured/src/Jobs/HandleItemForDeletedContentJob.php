<?php

namespace MetaFox\Featured\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Featured\Support\Constants;
use MetaFox\Platform\Jobs\AbstractJob;

class HandleItemForDeletedContentJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $entityType, protected int $entityId, protected ?string $title = null)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->updateTitle();
        $this->cancelRunningItem();
    }

    protected function cancelRunningItem(): void
    {
        $item = Item::query()
            ->where([
                'item_id'   => $this->entityId,
                'item_type' => $this->entityType,
            ])
            ->whereIn('status', [Constants::FEATURED_ITEM_STATUS_RUNNING, Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT])
            ->first();

        if (!$item instanceof Item) {
            return;
        }

        resolve(ItemRepositoryInterface::class)->markItemCancelledForDeletedContent($item);
    }

    protected function updateTitle(): void
    {
        if (!is_string($this->title)) {
            return;
        }

        Item::query()
            ->where([
                'item_type' => $this->entityType,
                'item_id'   => $this->entityId,
            ])
            ->update(['deleted_item_title' => $this->title]);
    }
}
