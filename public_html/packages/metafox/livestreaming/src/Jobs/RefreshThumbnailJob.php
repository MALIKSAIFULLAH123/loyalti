<?php

namespace MetaFox\LiveStreaming\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoRepository;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;

/**
 * This utility job helps refresh the external video thumbnail URLs.
 * Class RefreshThumbnailJob.
 */
class RefreshThumbnailJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $repository = $this->liveVideoRepository();
        $query      = $repository->getModel()->query()
            ->whereNotNull('image_file_id');


        foreach ($query->get() as $video) {
            Log::info(sprintf('Updating video #%s: %s', $video->entityId(), $video->live_stream_id));

            try {
                $thumbnailLink = $this->getThumbnailPlaybackUrl($video);
                if (empty($thumbnailLink)) {
                    Log::info('Could not find video thumbnail URL.');
                    continue;
                }

                $thumbnail       = $thumbnailLink ? $this->createThumbnailFromLink($video->user, $thumbnailLink) : null;
                $thumbnailFileId = $thumbnail instanceof StorageFile ? $thumbnail->entityId() : null;
                if (!$thumbnailFileId) {
                    Log::info('Could not create video thumbnail URL.');
                    continue;
                }

                $video->image_file_id = $thumbnailFileId;
                $video->saveQuietly();

                Log::info('Video thumbnail updated.');
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    public function getThumbnailPlaybackUrl(LiveVideo $liveVideo): ?string
    {
        if (!$liveVideo->playback) {
            return null;
        }

        $customThumbnailUrl  = Settings::get('livestreaming.custom_thumbnail_playback_url');
        $defaultThumbnailUrl = LiveVideoRepository::DEFAULT_THUMBNAIL_PLAYBACK;
        $thumbnailPlayback   = !empty($customThumbnailUrl) ? trim($customThumbnailUrl, '/') . '/' : $defaultThumbnailUrl;

        return $thumbnailPlayback . $liveVideo->playback->playback_id . '/thumbnail.png';
    }

    protected function createThumbnailFromLink(User $user, ?string $url): ?StorageFile
    {
        $response = Http::get($url);
        if (!$response->ok()) {
            return null;
        }
        $tempFile = sprintf('%s.%s', tempnam(sys_get_temp_dir(), 'metafox'), File::extension($url) ?? 'jpg');
        file_put_contents($tempFile, $response->body());

        $uploadedFile = UploadFile::pathToUploadedFile($tempFile);

        if (!$uploadedFile instanceof UploadedFile) {
            return null;
        }

        return upload()
            ->setStorage('photo')
            ->setPath('photo')
            ->setThumbSizes(ResizeImage::SIZE)
            ->setItemType('photo')
            ->setUser($user)
            ->storeFile($uploadedFile);
    }

    public function liveVideoRepository(): LiveVideoRepositoryInterface
    {
        return resolve(LiveVideoRepositoryInterface::class);
    }
}
