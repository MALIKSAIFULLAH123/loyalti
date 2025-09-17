<?php

namespace MetaFox\Featured\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Featured\Models\Transaction;
use MetaFox\Platform\Jobs\AbstractJob;

class HandleTransactionForDeletedContentJob extends AbstractJob
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
        if (!is_string($this->title)) {
            return;
        }

        Transaction::query()
            ->where([
                'item_type' => $this->entityType,
                'item_id'   => $this->entityId,
            ])
            ->update(['deleted_item_title' => $this->title]);
    }
}
