<?php

namespace MetaFox\Storage\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Repositories\FileRepositoryInterface;

class CleanUpDeletedStorageFiles extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $files = $this->getFileRepository()
            ->getModel()
            ->newModelQuery()
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', Carbon::now()->startOfDay())
            ->cursor();

        foreach ($files as $file) {
            if (!$file instanceof StorageFile) {
                continue;
            }

            $file->forceDelete();
        }
    }

    protected function getFileRepository(): FileRepositoryInterface
    {
        return resolve(FileRepositoryInterface::class);
    }
}
