<?php

namespace MetaFox\LiveStreaming\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoRepository;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Support\Facades\UserEntity;

class CheckViewerInterrupt extends AbstractJob
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
    public function __construct(protected int $live_video_id, protected int $user_id)
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

        $doc = app('firebase.firestore')->getDocument(LiveVideoRepository::FIREBASE_VIEW_COLLECTION, $liveVideo->stream_key);

        foreach ($doc['view'] as $view) {
            $user = $view->getData();
            if (($user['id'] == $this->user_id) && (time() - $user['last_ping']) > 60) {
                $context = UserEntity::getById($this->user_id)->detail;
                $repository->removeViewerCount($liveVideo, $context);
            }
        }
    }
}
