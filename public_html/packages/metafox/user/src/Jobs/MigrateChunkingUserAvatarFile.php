<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class MigrateChunkingUserAvatarFile extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected array $fileIds)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!count($this->fileIds)) {
            return;
        }

        $files       = StorageFile::query()
            ->whereIn('id', $this->fileIds)
            ->cursor();
        $notExistIds = [];
        $files->each(function (StorageFile $file) use (&$notExistIds) {
            if (!Storage::disk($file->target)->exists($file->path)) {
                $notExistIds[] = $file->id;
            }
        });

        if (!count($notExistIds)) {
            return;
        }

        $variantFiles = StorageFile::query()
            ->whereIn('origin_id', $notExistIds)
            ->where('variant', '=', '200x200')
            ->cursor();
        $upsert       = [];
        $variantFiles->each(function (StorageFile $file) use (&$upsert) {
            $upsert[] = [
                'id'   => $file->origin_id,
                'path' => $file->path,
            ];
        });
        if (!count($upsert)) {
            return;
        }

        StorageFile::query()->upsert($upsert, ['id'], ['path']);
    }
}
