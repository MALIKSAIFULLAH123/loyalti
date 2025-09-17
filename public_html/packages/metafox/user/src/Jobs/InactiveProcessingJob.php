<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\InactiveProcess;
use MetaFox\User\Repositories\InactiveProcessAdminRepositoryInterface;

class InactiveProcessingJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /**
         * @var InactiveProcessAdminRepositoryInterface $repository
         */
        $repository = resolve(InactiveProcessAdminRepositoryInterface::class);
        $model      = $repository->pickStartInactiveProcess();

        if (!$model instanceof InactiveProcess) {
            return;
        }

        $model->updateQuietly(['status' => InactiveProcess::SENDING_STATUS]);

        SendInactiveMailingJob::dispatch($model->entityId());
    }
}
