<?php

namespace MetaFox\EMoney\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Jobs\AbstractJob;

class ApprovePendingTransactionJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $transactions = Transaction::query()
            ->where('status', Support::TRANSACTION_STATUS_PENDING)
            ->where('source', Support::TRANSACTION_SOURCE_INCOMING)
            ->whereNotNull('available_at')
            ->where('available_at', '<=', Carbon::now())
            ->get();

        if (!$transactions->count()) {
            return;
        }

        $repository = resolve(TransactionRepositoryInterface::class);

        $transactions->each(function ($transaction) use ($repository) {
            $repository->approveTransaction($transaction);
        });
    }
}
