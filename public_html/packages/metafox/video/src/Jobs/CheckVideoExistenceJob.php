<?php

namespace MetaFox\Video\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Video\Repositories\VerifyProcessRepositoryInterface;
use MetaFox\Video\Repositories\VideoAdminRepositoryInterface;
use MetaFox\Video\Support\VideoSupport;

class CheckVideoExistenceJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @return string
     * @link https://laravel.com/docs/9.x/queues#unique-jobs
     */
    public function uniqueId(): string
    {
        return uniqid(__CLASS__);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $process = $this->processRepository()->pickProcess();

        if ($process === null) {
            return;
        }

        $process->updateQuietly(['status' => VideoSupport::PROCESSING_VERIFY_STATUS]);

        $extra    = $process->extra;
        $videoIds = Arr::get($extra, 'video_ids', []);
        if (!empty($videoIds)) {
            $videoIds = $process->last_id > 0
                ? array_filter($videoIds, function ($id) use ($process) {
                    return $id > $process->last_id;
                })
                : $videoIds;

            $videoIds = count($videoIds) <= $this->getTotalChunking()
                ? $videoIds
                : array_slice($videoIds, 0, $this->getTotalChunking());

            $this->repository()->handleSpecificVerification($videoIds, $process);
            return;
        }

        $query = $this->repository()->getModel()->newQuery();

        if ($process->last_id > 0) {
            $query->where('id', '>', $process->last_id);
        }

        $videos = $query->orderBy('id')->cursor();

        if ($videos->isEmpty()) {
            return;
        }

        $videoIds = $videos->slice(0, $this->getTotalChunking())->pluck('id')->toArray();

        $this->repository()->handleSpecificVerification($videoIds, $process);
    }

    protected function getTotalChunking(): int
    {
        return 100;
    }

    protected function repository(): VideoAdminRepositoryInterface
    {
        return resolve(VideoAdminRepositoryInterface::class);
    }

    protected function processRepository(): VerifyProcessRepositoryInterface
    {
        return resolve(VerifyProcessRepositoryInterface::class);
    }
}
