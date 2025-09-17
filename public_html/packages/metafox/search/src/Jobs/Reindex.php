<?php

namespace MetaFox\Search\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class Reindex extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SearchRepositoryInterface $searchRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected string $entity, protected int $page, protected int $limit)
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
        try {
            $object = resolve($this->entity);

            $models = $object::query()->forPage($this->page, $this->limit)->cursor();

            foreach ($models as $model) {
                if (!$model instanceof Content) {
                    continue;
                }

                if (!$model->isApproved()) {
                    continue;
                }

                try {
                    $this->getSearchRepository()->updatedBy($model);
                } catch (\Exception $exception) {
                    $this->logModelError($model, $exception);
                }
            }
        } catch (\Exception $exception) {
            Log::error(sprintf('Error when reindexing global search, Entity: %s', $this->entity), [$exception->getMessage()]);

            $this->fail($exception);
        }
    }

    /**
     * Get the value of searchRepository.
     */
    public function getSearchRepository(): SearchRepositoryInterface
    {
        if (empty($this->searchRepository)) {
            $this->searchRepository = resolve(SearchRepositoryInterface::class);
        }

        return $this->searchRepository;
    }

    protected function logModelError($model, \Exception $exception): void
    {
        $error = sprintf('Error when reindexing global search, Entity: %s', $this->entity);

        if ($model instanceof Content) {
            $error .= sprintf(', entityId: %s, entityType: %s', $model->entityId(), $model->entityType());
        }

        Log::error($error, [$exception->getMessage()]);
    }
}
