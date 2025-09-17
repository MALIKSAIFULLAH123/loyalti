<?php

namespace MetaFox\LiveStreaming\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteCategoryJob.
 * @ignore
 * @codeCoverageIgnore
 */
class CheckLiveVideoInterrupt extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use RepoTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $live_video_id)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $repository = $this->getLiveVideoRepository();

        $liveVideo = $repository->find($this->live_video_id);

        if ((time() - $liveVideo->last_ping) > 60) {
            $repository->stopLiveStream($this->live_video_id);
        }
    }
}
