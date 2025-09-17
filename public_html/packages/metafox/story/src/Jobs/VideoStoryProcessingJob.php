<?php

namespace MetaFox\Story\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;
use Prettus\Validator\Exceptions\ValidatorException;


/**
 * stub: packages/jobs/job-queued.stub
 */
class VideoStoryProcessingJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private StorageFile $file;

    private array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StorageFile $file, array $attributes)
    {
        parent::__construct();
        $this->file       = $file;
        $this->attributes = $attributes;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $service = StoryFacades::getDefaultServiceClass();
        $storyId = Arr::pull($this->attributes, 'story_id');

        try {
            $data = $service->processVideo($this->file);
            Arr::set($this->attributes, 'asset_id', Arr::get($data, 'asset_id'));
            $nextStep = Arr::get($data, 'next_step');

            match ($nextStep) {
                null, 'done' => $this->finalize($storyId, $data),
                default      => $this->next($service, $storyId, $this->attributes),
            };

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            Log::error(json_encode($th->getTrace()));
            $service->failProcessing(['story_id' => $storyId]);
            $this->fail();
        }
    }

    /**
     * Additional steps to support other services (if any).
     *
     * @param VideoServiceInterface $service
     * @param int                   $storyId
     * @param array<string, mixed>  $data
     *
     * @return void
     * @throws ValidatorException
     */
    protected function next(VideoServiceInterface $service, int $storyId, array $data): void
    {
        $this->storyRepository()->update($data, $storyId);

        app('events')->dispatch(
            'story.processing_next_step',
            [$service->getProviderType(), $storyId, $data],
            true,
        );
    }

    /**
     * @param int                  $storyId
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function finalize(int $storyId, array $data): void
    {
        $duration = (int) Arr::get($data, 'duration', Arr::get($this->attributes, 'duration', StoryFacades::getConfiguredVideoDuration()));
        Arr::set($this->attributes, 'duration', $duration);
        Arr::set($this->attributes, 'in_process', StorySupport::STATUS_VIDEO_READY);
        Arr::set($this->attributes, 'image_file_id', Arr::get($data, 'image_file_id'));
        Arr::set($this->attributes, 'video_file_id', Arr::get($data, 'video_file_id'));
        $this->storyRepository()->doneProcessVideo($storyId, $this->attributes);

        // Delete temp file after done
        upload()->rollUp($this->file->entityId());
    }

    protected function storyRepository(): StoryRepositoryInterface
    {
        return resolve(StoryRepositoryInterface::class);
    }
}
