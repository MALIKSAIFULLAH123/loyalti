<?php

namespace MetaFox\Video\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use Throwable;

/**
 * Class VideoEncodingJob.
 */
class VideoProcessingJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private StorageFile $file;

    private int $videoId;

    public function retryAfter(): int
    {
        return $this->timeout;
    }

    /**
     * Create a new job instance.
     *
     * @param StorageFile $file
     * @param int         $videoId
     */
    public function __construct(StorageFile $file, int $videoId)
    {
        parent::__construct();
        $this->file    = $file;
        $this->videoId = $videoId;
        $this->timeout = $this->providerManager()->getProcessingTimeout();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $service = $this->getVideoService();
        if (!$service instanceof VideoServiceInterface) {
            return;
        }
        if (!$this->getVideoRepositoryInterface()->markVideoProcessing($this->videoId)) {
            return;
        }

        try {
            $data     = $service->processVideo($this->file);
            $nextStep = Arr::get($data, 'next_step');

            match ($nextStep) {
                null, 'done' => $this->finalize($this->videoId, $data),
                default      => $this->next($service, $this->videoId, $data),
            };
        } catch (\Throwable $th) {
            $this->fail($th);
        }
    }

    /**
     * Additional steps to support other services (if any).
     * @param VideoServiceInterface $service
     * @param int                   $videoId
     * @param array<string, mixed>  $data
     * @return void
     * @throws ValidatorException
     */
    protected function next(VideoServiceInterface $service, int $videoId, array $data): void
    {
        $this->getVideoRepositoryInterface()->update($data, $videoId);

        app('events')->dispatch(
            'video.processing_next_step',
            [$service->getProviderType(), $videoId, $data],
            true,
        );
    }

    /**
     * @param int                  $videoId
     * @param array<string, mixed> $data
     * @return void
     */
    protected function finalize(int $videoId, array $data): void
    {
        $this->getVideoRepositoryInterface()->doneProcessVideo($videoId, $data);

        // Delete temp file after done
        upload()->rollUp($this->file->entityId());
    }

    protected function getVideoService(): ?VideoServiceInterface
    {
        try {
            return $this->providerManager()->getDefaultServiceClass();
        } catch (\Throwable $th) {
            app('events')->dispatch('video.processing_failed', [['video_id' => $this->videoId]], true);

            return null;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        if ($exception instanceof Throwable) {
            Log::error($exception->getMessage());
            Log::error(json_encode($exception->getTrace()));
        }

        $service = $this->getVideoService();
        if (!$service instanceof VideoServiceInterface) {
            return;
        }

        if ($exception instanceof MaxAttemptsExceededException) {
            return;
        }

        $service->failProcessing(['video_id' => $this->videoId]);
    }

    protected function getVideoRepositoryInterface(): VideoRepositoryInterface
    {
        return resolve(VideoRepositoryInterface::class);
    }

    protected function providerManager(): ProviderManagerInterface
    {
        return resolve(ProviderManagerInterface::class);
    }
}
