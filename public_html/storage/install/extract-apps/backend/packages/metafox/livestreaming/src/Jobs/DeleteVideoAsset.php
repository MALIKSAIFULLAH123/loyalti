<?php

namespace MetaFox\LiveStreaming\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteVideoAsset.
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteVideoAsset extends AbstractJob
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
    public function __construct(protected $asset_id, protected $live_stream_id)
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
        $service       = $this->getServiceManager();
        $serviceName   = $service->getDefaultServiceName();
        $serviceDriver = $service->getDefaultServiceProvider();

        if ($serviceDriver && $serviceName == 'mux') {
            if (!empty($this->asset_id)) {
                $url = 'assets/' . $this->asset_id;
                $serviceDriver->executeApi($url, 'DELETE');
            }
            if (!empty($this->live_stream_id)) {
                $liveUrl = 'live-streams/' . $this->live_stream_id;
                $serviceDriver->executeApi($liveUrl, 'DELETE');
            }
        }
    }
}
