<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Http\Resources\v1;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Video\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

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
    public function getMobileSettings(CategoryRepositoryInterface $repository): array
    {
        return [
            'video_service_is_ready' => $this->checkReadyService(),
            'categories'             => new CategoryItemCollection($repository->getCategoryForFilter()),
        ];
    }

    public function getWebSettings(): array
    {
        return [
            'video_service_is_ready' => $this->checkReadyService(),
        ];
    }

    protected function checkReadyService(): bool
    {
        return Settings::get('video.enable_video_uploads', true);
    }
}
