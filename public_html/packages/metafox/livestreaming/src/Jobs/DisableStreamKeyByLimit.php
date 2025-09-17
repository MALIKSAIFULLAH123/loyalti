<?php

namespace MetaFox\LiveStreaming\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Models\UserStreamKey;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoRepository;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteCategoryJob.
 * @ignore
 * @codeCoverageIgnore
 */
class DisableStreamKeyByLimit extends AbstractJob
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
    public function __construct(protected int $live_id, protected string $live_stream_id = '')
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
        /** @var LiveVideo $liveVideo */
        $liveVideo = $this->getLiveVideoRepository()->getModel()->newQuery()->where('id', $this->live_id)->first();
        if (!$liveVideo) {
            $service       = $this->getServiceManager();
            $serviceName   = $service->getDefaultServiceName();
            $serviceDriver = $service->getDefaultServiceProvider();
            if ($serviceDriver && $serviceName == LiveVideoRepository::SERVICE_MUX) {
                $serviceDriver->executeApi('live-streams/' . $this->live_stream_id . '/disable', 'PUT');
            }
        } else {
            /** @var UserStreamKey $streamKey */
            $streamKey = $this->getUserStreamKeyRepository()->getModel()->newQuery()->where('stream_key', $liveVideo->stream_key)->first();
            if ($streamKey && !$streamKey->connected_from) {
                return; // Disconnected before
            }
            $this->getLiveVideoRepository()->stopLiveStream($liveVideo->id);
        }

        app('events')->dispatch('livestreaming.disable_stream_key', [$this->live_id, $this->live_stream_id], true);
    }
}
