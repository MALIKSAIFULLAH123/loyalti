<?php

namespace MetaFox\Video\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Contracts\Support\VideoSupportInterface;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Models\Video as VideoModel;

/**
 * @method static bool                 deleteVideoByAssetId(string $assetId)
 * @method static array<string, mixed> parseLink(string $url)
 * @method static string               parseVideoTitle(string $content)
 * @method static array                getStatusTexts(VideoModel $video)
 * @method static array                getMatureContentOptions()
 * @method static array                getAllowMatureContent()
 * @method static array|null           getMatureDataConfig(User $context, VideoModel $video)
 * @method static string|null          getDataWithContext(User $user, VideoModel $video, string $type = 'images')
 * @method static array                getStatusVerifyProcessTexts(VerifyProcess $model)
 *
 * @see VideoSupport
 */
class Video extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return VideoSupportInterface::class;
    }
}
