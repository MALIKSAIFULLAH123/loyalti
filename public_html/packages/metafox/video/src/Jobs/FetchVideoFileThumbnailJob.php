<?php

namespace MetaFox\Video\Jobs;

use FFMpeg\Coordinate\TimeCode;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\FFMPEG\Support\Providers\FFMPEG;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

/**
 * Class FetchVideoFileThumbnailJob.
 */
class FetchVideoFileThumbnailJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $videoId;

    private string $fileLink;

    /**
     * Create a new job instance.
     *
     * @param StorageFile $file
     * @param int         $videoId
     */
    public function __construct(int $videoId, string $fileLink)
    {
        parent::__construct();
        $this->videoId  = $videoId;
        $this->fileLink = $fileLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $video = $this->getVideo($this->videoId);
        if (!$video instanceof Video) {
            return;
        }

        try {
            // Download file through http client since fopen_wrapper param allow_url_fopen = 0
            $response = Http::timeout(180)->get($this->fileLink);

            if (!$response->successful()) {
                return;
            }

            $tempFile = sprintf('%s_%s.mp4', tempnam(sys_get_temp_dir(), 'metafox'), 'video_file');
            file_put_contents($tempFile, $response->body());

            $ffmpeg    = resolve(FFMPEG::class);
            $videoFile = $ffmpeg->open($tempFile);
            $imagePath = tempnam(sys_get_temp_dir(), 'metafox') . '_thumbnail.jpg';
            $duration  = (int) $videoFile->getFFProbe()->format($tempFile)->get('duration');

            if ($duration <= 0) {
                return;
            }

            $frame = $videoFile->frame(TimeCode::fromSeconds($duration / 2));
            $frame->save($imagePath);

            $image = upload()
                ->setStorage('photo')
                ->setPath('video')
                ->setThumbSizes(['500'])
                ->setItemType('photo')
                ->setUser($video->user)
                ->storeFile(UploadFile::pathToUploadedFile($imagePath));

            $video->update(['image_file_id' => $image->entityId()]);
        } catch (\Exception $error) {
            Log::error($error->getMessage());
            $this->fail();
        }
    }

    protected function getVideo(int $videoId): Video
    {
        $video = null;
        try {
            $video = resolve(VideoRepositoryInterface::class)->find($videoId);
        } catch (\Exception $error) {
            Log::error(__METHOD__ . ' : ' . 'cannot find video.');
            Log::error($error->getMessage());
        }

        return $video;
    }
}
