<?php

namespace MetaFox\Follow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\Notify;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class SendFollowerNotification extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var FollowRepositoryInterface $followRepository */
        $followRepository = resolve(FollowRepositoryInterface::class);

        $model = $this->model;

        if (!$model instanceof Content) {
            return;
        }

        $followers = $followRepository->getUserFollowers($model->owner);

        if (!method_exists($model, 'toFollowerNotification')) {
            return;
        }

        $dataFollowerNotification = $model->toFollowerNotification();
        $type                     = Arr::get($dataFollowerNotification, 'type');

        if ($type === null) {
            return;
        }

        $handlerClass = Notify::getHandler($type);

        if (empty($handlerClass)) {
            return;
        }

        $exceptUsers = Arr::get($dataFollowerNotification, 'exclude', []);
        $collection  = collect($exceptUsers);
        $followers   = $followers->reject(function ($follower) use ($model, $collection) {
            if ($collection->isNotEmpty()) {
                $exceptUsers = $collection->pluck('id')->toArray();

                if (in_array($follower->id, $exceptUsers)) {
                    return true;
                }
            }

            return !PrivacyPolicy::checkPermission($follower, $model);
        })->all();

        Notification::send($followers, new $handlerClass($model));
    }
}
