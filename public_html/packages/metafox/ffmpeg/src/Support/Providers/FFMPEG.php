<?php

namespace MetaFox\FFMPEG\Support\Providers;

use FFMpeg\Coordinate\FrameRate;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Driver\FFMpegDriver as BaseDriver;
use FFMpeg\FFProbe;
use MetaFox\FFMPEG\Support\Drivers\FFMPEGDriver as Driver;
use FFMpeg\Format\FormatInterface;
use MetaFox\FFMPEG\Support\Media\Audio;
use MetaFox\FFMPEG\Support\Media\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Core\Support\FileSystem\UploadFile;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Storage\Models\StorageFile;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class FFMPEG implements VideoServiceInterface
{
    /**
     * The FFMPEG driver.
     */
    private Driver $driver;
    private string $itemType;
    private array  $thumbSize;

    public const PROVIDER_TYPE = 'ffmpeg';
    public const STATUS_READY  = 0;

    public function getProviderType(): string
    {
        return self::PROVIDER_TYPE;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->itemType  = Arr::get($config, 'item_type', 'video');
        $this->thumbSize = Arr::get($config, 'thumb_size', ResizeImage::SIZE);
        $config          = [
            'ffmpeg.binaries'  => Settings::get('ffmpeg.binaries'),
            'ffprobe.binaries' => Settings::get('ffmpeg.ffprobe_binaries'),
            'ffmpeg.threads'   => Settings::get('ffmpeg.threads'),
            'timeout'          => Settings::get('ffmpeg.timeout'),
        ];

        if (
            empty($config['ffmpeg.binaries'])
            || empty($config['ffprobe.binaries'])
        ) {
            abort(500, 'Missing FFMPEG Configuration! Please recheck settings!');
        }

        $this->driver = $this->createDriver($config, Log::channel('video'));
    }

    /**
     * @inheritDoc
     */
    public function processVideo(StorageFile $file): array
    {
        // Reading video file
        $localFile = app('storage')->getAs($file->entityId());

        $outputPath = sprintf('%s_%s.mp4', tempnam(sys_get_temp_dir(), 'metafox'), File::name($file->original_name));
        $video      = $this->open($localFile);

        $image          = null;
        $imagePath      = tempnam(sys_get_temp_dir(), 'metafox') . '_thumbnail.jpg';
        $videoStream    = $video->getFFProbe()->streams($localFile)->videos()->first();
        $duration       = $videoStream->get('duration', 0);
        $streamSideData = $videoStream->get('side_data_list', []);
        $matrixData     = collect($streamSideData)->filter(fn ($data) => isset($data['side_data_type']) && 'Display Matrix' == $data['side_data_type'])->values()->first();
        $rotation       = Arr::get($matrixData, 'rotation', 0);

        $isRotated90Degree = in_array(abs((int) $rotation), [90, 270]);
        $dimension         = $videoStream?->getDimensions();
        $width             = $dimension ? $dimension->getWidth() : 0;
        $height            = $dimension ? $dimension->getHeight() : 0;
        if ((int) $duration > 0) {
            $frame = $video->frame(TimeCode::fromSeconds($duration / 2));
            $frame->save($imagePath);

            $image = upload()
                ->setStorage('photo')
                ->setPath($this->itemType)
                ->setThumbSizes($this->thumbSize)
                ->setItemType('photo')
                ->setUser($file->user)
                ->storeFile(UploadFile::pathToUploadedFile($imagePath));
        }
        $video->save($this->buildFormat(), $outputPath);

        $videoFile = upload()
            ->setStorage('video')
            ->setPath($this->itemType)
            ->setItemType($this->itemType)
            ->setUser($file->user)
            ->storeFile(UploadFile::pathToUploadedFile($outputPath));

        $this->cleanUpTemp([$localFile, $outputPath, $imagePath]);

        return [
            'image_file_id' => $image instanceof StorageFile ? $image->entityId() : null,
            'video_file_id' => $videoFile instanceof StorageFile ? $videoFile->entityId() : null,
            'duration'      => $duration,
            'resolution_x'  => (string) ($isRotated90Degree ? $height : $width),
            'resolution_y'  => (string) ($isRotated90Degree ? $width : $height),
            'in_process'    => self::STATUS_READY,
        ];
    }

    /**
     * @param  Request $request
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleWebhook(Request $request): bool
    {
        return true; // No webhook needed
    }

    protected function buildFormat(): FormatInterface
    {
        $format = new \MetaFox\FFMPEG\Support\Formats\X264();

        return $format
            ->customized()
            ->setAdditionalParameters([
                '-crf',
                23,
            ])
            ->setKiloBitrate(0)
            ->setBFramesSupport(false);
    }

    private function cleanUpTemp(array $files)
    {
        foreach ($files as $file) {
            if (file_exists($file)) { // ensure file exists or add @unlink file.
                @unlink($file);
            }
        }
    }

    public function executeApi(string $apiName, string $method = 'GET', bool $returnTransfer = false, string $postFields = ''): mixed
    {
        return null;
    }

    public function getLiveServerUrl(): string
    {
        return '';
    }

    public function getThumbnailPlayback(): string
    {
        return '';
    }

    public function getVideoPlayback(): string
    {
        return '';
    }

    public function isValidConfiguration(): bool
    {
        return Settings::get('ffmpeg.binaries') && Settings::get('ffmpeg.ffprobe_binaries');
    }

    public function open(string $path): Audio|Video
    {
        return $this->driver->open($path);
    }

    public function failProcessing(array $params): void
    {
        app('events')->dispatch("{$this->itemType}.processing_failed", [$params], true);
    }

    public function testConfig(): bool
    {
        $ffmpegBinaries  = Settings::get('ffmpeg.binaries', '');
        $ffprobeBinaries = Settings::get('ffmpeg.ffprobe_binaries', '');

        if (empty($ffmpegBinaries) || !File::exists($ffmpegBinaries)) {
            return false;
        }

        if (empty($ffprobeBinaries) || !File::exists($ffprobeBinaries)) {
            return false;
        }

        return true;
    }

    /**
     * Initialize the ffmpeg driver.
     *
     * @param  array<string, mixed> $configuration
     * @param  LoggerInterface      $logger
     * @return Driver
     */
    protected function createDriver($configuration, LoggerInterface $logger): Driver
    {
        $ffmpeg = BaseDriver::create($logger, $configuration);
        $probe  = FFProbe::create($configuration, $logger, null);

        return resolve(Driver::class, ['ffmpeg' => $ffmpeg, 'ffprobe' => $probe]);
    }
}
