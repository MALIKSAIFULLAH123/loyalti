<?php

namespace MetaFox\Activity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateFeedContent extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $typeIdList = [
            'activity_post',
        ];

        foreach ($typeIdList as $key => $value) {
            $modelClass = $this->getModelClass($value);

            if (!$modelClass) {
                continue;
            }

            $typeId = is_int($key) ? $value : $key;

            $feeds = resolve(FeedRepositoryInterface::class)->getMissingContentFeed($typeId);

            if (!$feeds->count()) {
                continue;
            }

            $collections = $feeds->chunk(100);

            foreach ($collections as $collection) {
                MigrateChunkingFeedContent::dispatch($modelClass, $collection->pluck('id')->toArray());
            }
        }
    }

    protected function getModelClass(string $entityType): ?string
    {
        $modelClass = Relation::getMorphedModel($entityType);

        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }

        return $modelClass;
    }
}
