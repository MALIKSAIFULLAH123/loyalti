<?php

namespace MetaFox\Video\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\Facade\Video;

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
        $repository = $this->videoRepository();
        $query      = $repository->getModel()->query()
            ->whereNotNull('video_url');


        foreach ($query->get() as $video) {
            Log::info(sprintf('Updating video #%s: %s', $video->entityId(), $video->video_url));

            try {
                $data          = $this->validateLink($video->video_url);
                $thumbnailLink = Arr::get($data, 'thumbnail', null);
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

    private function videoRepository(): VideoRepositoryInterface
    {
        return resolve(VideoRepositoryInterface::class);
    }

    /**
     * @param string|null $url
     * @return array<string, mixed>
     * @throws ValidationException
     */
    protected function validateLink(?string $url): array
    {
        if (!$url) {
            return [];
        }

        $data = Video::parseLink($url);

        return [
            'title'      => $data['title'] ?? null,
            'text'       => $data['description'] ?? null,
            'embed_code' => $data['embed_code'] ?? null,
            'duration'   => $data['duration'] ?? null,
            'thumbnail'  => $data['image'] ?? null,
            'is_file'    => $data['is_file'] ?? false,
            'in_process' => 0,
            'video_url'  => $data['link'] ?? $url,
        ];
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
            ->setPath('video')
            ->setThumbSizes(['500'])
            ->setItemType('photo')
            ->setUser($user)
            ->storeFile($uploadedFile);
    }
}
