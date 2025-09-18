<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Http\Resources\v1;

use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    use RepoTrait;

    public function getWebSettings(): array
    {
        $siteUrl        = config('app.url');
        $userId         = user()->entityId();
        $hashPassword   = md5($userId . $siteUrl);
        $serviceManager = $this->getServiceManager();

        return [
            'user_firebase_password'   => $hashPassword,
            'user_firebase_email'      => 'user_0' . $userId . '@' . preg_replace('/(http|https):\/\//', '', $siteUrl),
            'streaming_service_enable' => (bool) $serviceManager->getDefaultServiceProvider()?->isValidConfiguration(),
            'socketUrl'                => Settings::get('livestreaming.webcam_websocket_url'),
        ];
    }

    public function getMobileSettings(): array
    {
        $serviceManager = $this->getServiceManager();
        $driver         = $serviceManager->getDefaultServiceName();
        $serviceHelper  = $serviceManager->getDefaultServiceProvider();
        $siteUrl        = config('app.url');
        $userId         = user()->entityId();
        $hashPassword   = md5($userId . $siteUrl);
        $settings       = [
            'user_firebase_password' => $hashPassword,
            'user_firebase_email'    => 'user_0' . $userId . '@' . preg_replace('/(http|https):\/\//', '', $siteUrl),

        ];
        $settings[$driver]                           = $this->getDriverSetting($driver);
        $settings[$driver]['video_playback_url']     = $serviceHelper?->getVideoPlayback();
        $settings[$driver]['thumbnail_playback_url'] = $serviceHelper?->getThumbnailPlayback();
        $settings['streaming_service_enable']        = (bool) $serviceHelper?->isValidConfiguration();

        return $settings;
    }

    private function getDriverSetting(string $driver): ?array
    {
        $settings = Settings::get("$driver.livestreaming") ?? [];
        /*
         * @deprecated in MetaFox 5.2
         */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.5', '<')) {
            return $settings;
        }

        return array_filter($settings, function ($value, $key) {
            return !preg_match('/(\_)?secret(\_)?/', $key);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
