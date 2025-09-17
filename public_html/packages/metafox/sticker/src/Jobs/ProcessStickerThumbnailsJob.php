<?php

namespace MetaFox\Sticker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\ResizeImageInterface;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;


/**
 * stub: packages/jobs/job-queued.stub
 */
class ProcessStickerThumbnailsJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $data = Sticker::query()
            ->whereNull('thumbnail_file_id')
            ->where('is_deleted', '<>', 1)
            ->get();

        if ($data->isEmpty()) {
            return;
        }

        foreach ($data as $item) {
            if (!$item instanceof Sticker) {
                continue;
            }

            try {
                $this->createThumb($item);
            } catch (\Throwable $e) {}
        }
    }

    private function createThumb(Sticker $sticker): void
    {
        $tempFile = upload()->getFile($sticker->image_file_id);
        upload()->rollUp($sticker->image_file_id);

        if ($tempFile->original_name === null) {
            $tempFile->update(['original_name' => Str::afterLast($tempFile->path, '/')]);
            $tempFile->refresh();
        }

        $image = app('storage')->asUploadedFile($sticker->image_file_id);
        $user  = resolve(UserRepositoryInterface::class)->getSuperAdmin();

        if ($tempFile->user_id) {
            $user = $tempFile->user;
        }

        $file = $this->resizeImage()
            ->setImage($image)
            ->setUser($user)
            ->setPath('sticker/' . $sticker->entityId())
            ->setOriginalName($tempFile->original_name)
            ->setExtra([
                'item_type' => $sticker->entityType(),
            ])
            ->createFile();

        $sticker->update(['thumbnail_file_id' => $file->entityId()]);
    }

    protected function resizeImage(): ResizeImageInterface
    {
        return resolve(ResizeImageInterface::class);
    }
}
