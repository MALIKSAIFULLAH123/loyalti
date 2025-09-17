<?php

namespace MetaFox\Layout\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Layout\Models\Build;
use MetaFox\Layout\Repositories\BuildRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class BuildRepository.
 */
class BuildRepository extends AbstractRepository implements BuildRepositoryInterface
{
    public function model()
    {
        return Build::class;
    }

    /**
     * Mark all pending, processing, 'sending.
     * @return void
     */
    public function checkExpiredTasks(): void
    {
        $last = $this->findLast();

        if (!$last) {
            return;
        }

        // check if there is any pending, processing to deprecated.

        /** @var Collection<Build> $inProcessing */
        $inProcessing = $this->getModel()
            ->newQuery()
            ->where('id', '<', $last->id)
            ->whereIn('bundle_status', ['pending', 'extracting', 'processing', 'sending'])
            ->get();

        foreach ($inProcessing as $task) {
            if ($task->expired()) {
                $task->bundle_status = 'deprecated';
            } else {
                $task->bundle_status = 'cancelled';
            }
            $task->saveQuietly();
        }
    }

    /**
     * @return ?Build
     */
    public function findLast(): ?Build
    {
        /** @var ?Build $task */
        $task = $this->getModel()->newQuery()->orderBy('id', 'DESC')->first();

        return $task;
    }

    public function getByBuildId(string $buildId): Build
    {
        /** @var Build $task */
        $task = $this->getModel()->newQuery()->where('job_id', '=', $buildId)->firstOrFail();

        return $task;
    }
}
