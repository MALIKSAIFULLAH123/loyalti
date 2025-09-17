<?php

namespace MetaFox\ChatPlus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class ProcessSyncUsers extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $min, protected int $max)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getChatServer()->exportUsers($this->min, $this->max);
    }

    public function getChatServer(): ChatServerInterface
    {
        return resolve(ChatServerInterface::class);
    }
}
