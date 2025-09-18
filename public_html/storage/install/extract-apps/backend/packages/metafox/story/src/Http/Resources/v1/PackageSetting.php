<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Story\Http\Resources\v1;

use MetaFox\Story\Support\Facades\StoryFacades;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub
 */

/**
 * Class PackageSetting
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'video_duration'         => StoryFacades::getConfiguredVideoDuration(),
            'video_service_is_ready' => StoryFacades::checkReadyService(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'privacy'                => StoryFacades::getPrivacyOptions(),
            'video_duration'         => StoryFacades::getConfiguredVideoDuration(),
            'font_styles'            => StoryFacades::getFontStyleOptions(),
            'lifespan'               => StoryFacades::getLifespanOptions(),
            'lifespan_default'       => StoryFacades::getLifespanDefault(),
            'video_service_is_ready' => StoryFacades::checkReadyService(),
        ];
    }
}
