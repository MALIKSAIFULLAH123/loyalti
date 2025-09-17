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
class WarningLiveTimeLimit extends AbstractJob
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
    public function __construct(protected int $live_id)
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
        if (!$liveVideo || !$liveVideo->is_streaming) {
            return;
        }
        $document = app('firebase.firestore')->getDocument(LiveVideoRepository::FIREBASE_COLLECTION, $liveVideo->stream_key);
        if (!$document) {
            return;
        }
        $document['time_limit_warning'] = 1;
        app('firebase.firestore')->addDocument(LiveVideoRepository::FIREBASE_COLLECTION, $liveVideo->stream_key, $document);
    }
}
