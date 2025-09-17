<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Translation\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getSiteSettings(): array
    {
        return [
            'enable_translate' => ['value' => true],
        ];
    }

    public function getEvents(): array
    {
        return [
            'packages.installed'                => [
                PackageInstalledListener::class,
            ],
            'translation.translate'             => [
                TranslatingListener::class,
            ],
            'translation.clear_translated_text' => [
                ClearTranslatedTextListener::class,
            ],
            'models.notify.deleted'             => [
                ModelDeletedListener::class,
            ],
        ];
    }
}
