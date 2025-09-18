<?php

namespace MetaFox\Page\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Page\Models\Activity;
use MetaFox\Page\Repositories\ActivityRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteActivitiesJob.
 *
 * @ignore
 */
class DeleteActivitiesJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * DeleteActivitiesJob constructor.
     *
     * @param array $attributes
     */
    public function __construct(protected array $attributes)
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
        /**
         * @var ActivityRepositoryInterface $activityRepositor
         */
        $activityRepository = resolve(ActivityRepositoryInterface::class);

        $activityRepository->getModel()->newModelQuery()
            ->where($this->attributes)
            ->each(function (Activity $model) {
                $model?->item?->delete();
                $model?->delete();
            });
    }
}
