<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\InactiveProcess;
use MetaFox\User\Models\InactiveProcessData;
use MetaFox\User\Notifications\ProcessMailingInactiveUser;
use MetaFox\User\Repositories\InactiveProcessAdminRepositoryInterface;

class SendInactiveMailingJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $processId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $repository      = resolve(InactiveProcessAdminRepositoryInterface::class);
        $inactiveProcess = $repository->find($this->processId);

        if (!$inactiveProcess instanceof InactiveProcess) {
            return;
        }

        $pendingProcess = $inactiveProcess->pendingProcess();

        if ($inactiveProcess->round < $pendingProcess->count() && $inactiveProcess->round > 0) {
            $pendingProcess->limit($inactiveProcess->round);
        }

        if ($inactiveProcess->isStopped()) {
            return;
        }

        foreach ($pendingProcess->cursor() as $item) {
            if (!$item instanceof InactiveProcessData) {
                continue;
            }

            if ($item->isStopped()) {
                return;
            }

            if ($inactiveProcess->total_sent >= $inactiveProcess->total_users) {
                $inactiveProcess->updateQuietly([
                    'status' => InactiveProcess::COMPLETED_STATUS,
                ]);
                return;
            }

            Notification::send($item->user, new ProcessMailingInactiveUser($item->user));

            $item->updateQuietly(['status' => InactiveProcess::COMPLETED_STATUS]);

            $inactiveProcess->total_sent++;
            $inactiveProcess->saveQuietly();
            $inactiveProcess->refresh();
        }

        $status = match ($inactiveProcess->total_sent == $inactiveProcess->total_users) {
            true  => InactiveProcess::COMPLETED_STATUS,
            false => InactiveProcess::PENDING_STATUS
        };

        $inactiveProcess->updateQuietly([
            'status' => $status,
        ]);
    }
}
