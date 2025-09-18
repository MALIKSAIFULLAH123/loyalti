<?php

namespace MetaFox\Video\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Video\Contracts\Support\VideoSupportInterface;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class VideoSupport implements VideoSupportInterface
{
    private VideoRepositoryInterface $repository;

    public const PENDING_VERIFY_STATUS    = 'pending';
    public const PROCESSING_VERIFY_STATUS = 'processing';
    public const COMPLETED_VERIFY_STATUS  = 'completed';
    public const STOPPED_VERIFY_STATUS    = 'stopped';

    public const SUPPORTED_PLATFORMS = [
        'Facebook',
        'YouTube',
        'Vimeo',
        'TikTok',
        'Rumble',
    ];

    public function __construct(VideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function deleteVideoByAssetId(string $assetId): bool
    {
        return $this->repository->deleteVideoByAssetId($assetId);
    }

    public function parseLink(string $url): array
    {
        $key  = 'video_url_' . md5($url);
        $data = cache()->remember($key, 300, function () use ($url) {
            $data = $this->pingVideoFileURL($url);

            if (empty($data)) {
                return app('events')->dispatch('core.parse_url', [$url], true);
            }
        });

        return is_array($data) ? $data : [];
    }

    protected function pingVideoFileURL(string $url): array
    {
        $headers = null;
        try {
            $response = Http::head($url);
            if (!$response->successful()) {
                return [];
            }

            $headers = $response->headers();
        } catch (\Throwable $th) {
            return [];
        }

        if (!$headers) {
            return [];
        }

        $isVideo      = false;
        $contentType  = 'video/mp4';
        $contentTypes = Arr::get($headers, 'Content-Type') ?? [];

        foreach ($contentTypes as $type) {
            if (preg_match('#^video\/(\S+)$#i', $type)) {
                $isVideo     = true;
                $contentType = $type;
                break;
            }
        }

        if (!$isVideo) {
            return [];
        }

        $embed = sprintf('<video controls width="500"><source src="%s" type="%s"></video>', $url, $contentType);

        return [
            'resource_name' => 'link',
            'title'         => basename($url),
            'description'   => null,
            'image'         => null,
            'is_image'      => false,
            'is_video'      => $isVideo,
            'is_file'       => true,
            'link'          => $url,
            'embed_code'    => $embed,
            'duration'      => null,
            'host'          => parse_url($url, PHP_URL_HOST),
            'width'         => null,
            'height'        => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function parseVideoTitle(string $content): string
    {
        foreach ($this->getMentionPatterns() as $item) {
            $content = preg_replace_callback($item, function () {
                return '';
            }, $content);
        }

        $content = trim($content);

        $title = MetaFoxConstant::EMPTY_STRING;
        if (empty($content)) {
            return $title;
        }

        $lines = explode(PHP_EOL, $content);

        if (!is_array($lines)) {
            return $title;
        }

        foreach ($lines as $line) {
            if (is_string($line) && !empty($line)) {
                $title = $line;
                break;
            }
        }

        $maximumCharacters = Settings::get('video.maximum_name_length', 100);
        if (mb_strlen($title) <= $maximumCharacters) {
            return $title;
        }

        return parse_output()->limit($title, $maximumCharacters);
    }

    protected function getMentionPatterns(): array
    {
        $patterns = app('events')->dispatch('core.mention.pattern');
        $results  = [];
        foreach ($patterns as $pattern) {
            if (!is_array($pattern)) {
                continue;
            }

            foreach ($pattern as $item) {
                $results[] = $item;
            }
        }

        return $results;
    }

    public function getStatusTexts(Video $video): array
    {
        if ($video->is_processing) {
            return [
                'label' => __p('core::phrase.processing'),
                'color' => null,
            ];
        }

        if ($video->is_failed) {
            return [
                'label' => __p('core::phrase.failed'),
                'color' => null,
            ];
        }

        if ($video->isApproved()) {
            return [
                'label' => __p('core::phrase.approved'),
                'color' => null,
            ];
        }

        return [
            'label' => __p('core::phrase.pending'),
            'color' => null,
        ];
    }

    public function getMatureContentOptions(): array
    {
        return [
            ['label' => __p('video::phrase.yes_with_strict'), 'value' => Video::MATURE_CONTENT_STRICT],
            ['label' => __p('video::phrase.yes_with_warning'), 'value' => Video::MATURE_CONTENT_WARNING],
            ['label' => __p('core::phrase.no'), 'value' => Video::MATURE_CONTENT_NO],
        ];
    }

    public function getAllowMatureContent(): array
    {
        return Arr::pluck($this->getMatureContentOptions(), 'value');
    }

    public function getMatureDataConfig(User $context, Video $video): ?array
    {
        if (policy_check(VideoPolicy::class, 'viewMatureContent', $context, $video)) {
            return null;
        }

        $minAge       = (int) $context->getPermissionValue('video.mature_video_age_limit');
        $matureConfig = null;
        $mature       = $video->mature;

        if ($mature != Video::MATURE_CONTENT_NO) {
            $title        = $mature == Video::MATURE_CONTENT_STRICT ? __p('video::phrase.mature_strict_title') : __p('video::phrase.mature_warning_title');
            $message      = $mature == Video::MATURE_CONTENT_STRICT ? __p('video::phrase.mature_strict_message', ['age' => $minAge]) : __p('video::phrase.mature_warning_message');
            $matureConfig = [
                'title'   => $title,
                'message' => $message,
            ];

            if ($mature == Video::MATURE_CONTENT_WARNING) {
                $matureConfig['short_message'] = __p('video::phrase.mature_warning_short_message');
            }
        }

        return $matureConfig;
    }

    public function getDataWithContext(User $user, Video $video, string $type = 'images')
    {
        if (
            !policy_check(VideoPolicy::class, 'viewMatureContent', $user, $video)
            && $video->isStrictMatureContent()
        ) {
            return null;
        }

        return match ($type) {
            'images'      => $video->images,
            'destination' => $video->destination,
            'video_url'   => $video->video_url,
            'video_path'  => $video->video_path,
            default       => null,
        };
    }

    public function getStatusVerifyProcessTexts(VerifyProcess $model): array
    {
        $array = [
            self::PENDING_VERIFY_STATUS    => [
                'label' => __p('core::phrase.pending'),
                'color' => null,
            ],
            self::PROCESSING_VERIFY_STATUS => [
                'label' => __p('core::phrase.processing'),
                'color' => null,
            ],
            self::COMPLETED_VERIFY_STATUS  => [
                'label' => __p('user::phrase.completed'),
                'color' => null,
            ],
            self::STOPPED_VERIFY_STATUS    => [
                'label' => __p('user::phrase.status_stopped'),
                'color' => null,
            ],
        ];

        return $array[$model->status] ?? $array[self::PENDING_VERIFY_STATUS];
    }
}
