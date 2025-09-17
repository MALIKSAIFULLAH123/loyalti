<?php

namespace MetaFox\Featured\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Platform\Jobs\AbstractJob;

class HandleInvoiceForDeletedContentJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $entityType, protected int $entityId, protected ?string $title = null)
    {
        parent::__construct();
    }

    public function handle()
    {
        Invoice::query()
            ->where([
                'item_type' => $this->entityType,
                'item_id'   => $this->entityId,
                'status'    => Feature::getInitPaymentStatus(),
            ])
            ->delete();

        if (!is_string($this->title)) {
            return;
        }

        Invoice::query()
            ->where([
                'item_type' => $this->entityType,
                'item_id'   => $this->entityId,
            ])
            ->whereIn('status', [Feature::getCompletedPaymentStatus(), Feature::getPendingPaymentStatus()])
            ->update(['deleted_item_title' => $this->title]);
    }
}
