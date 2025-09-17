<?php

namespace MetaFox\Storage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Repositories\FileRepositoryInterface;

class DeleteStorageFilesJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function __construct(protected array $params = [])
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
        if (empty($this->params)) {
            return;
        }

        $target = Arr::get($this->params, 'target');
        $this->getFileRepository()
            ->getModel()
            ->newModelQuery()
            ->where('target', $target)
            ->delete();
    }

    protected function getFileRepository(): FileRepositoryInterface
    {
        return resolve(FileRepositoryInterface::class);
    }
}
