<?php

namespace MetaFox\Event\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Event\Models\Category;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteCategoryJob.
 */
class DeleteCategoryJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Category $category;

    protected int $newCategoryId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Category $category, int $newCategoryId)
    {
        parent::__construct();
        $this->category      = $category;
        $this->newCategoryId = $newCategoryId;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $categoryRepository = resolve(CategoryRepositoryInterface::class);
        $categoryRepository->deleteOrMoveToNewCategory($this->category, $this->newCategoryId);
    }
}
