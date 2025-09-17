<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class ExpiredUserBanJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $service = resolve(UserRepositoryInterface::class);

        $service->cleanUpExpiredBanData();
    }
}
