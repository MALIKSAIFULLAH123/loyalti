<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Localize\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'packages.installed'          => [
                PackageInstalledListener::class,
            ],
            'packages.deleted'            => [
                PackageDeletedListener::class,
            ],
            'models.notify.creating'      => [
                ModelCreatingListener::class,
            ],
            'models.notify.created'       => [
                ModelCreatedListener::class,
            ],
            'models.notify.updating'      => [
                ModelUpdatingListener::class,
            ],
            'models.notify.updated'       => [
                ModelUpdatedListener::class,
            ],
            'models.notify.deleted'       => [
                ModelDeletedListener::class,
            ],
            'localize.phrase.mass_create' => [
                PhraseMassCreateListener::class,
            ],
            'localize.phrase.mass_update' => [
                PhraseMassUpdateListener::class,
            ],
            'localize.phrase.mass_delete' => [
                PhraseMassDeleteListener::class,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'disable_translation'     => [
                'config_name' => 'localize.disable_translation',
                'env_var'     => 'MFOX_DISABLE_TRANSLATION',
                'value'       => false,
            ],
            'display_translation_key' => [
                'config_name' => 'localize.display_translation_key',
                'env_var'     => 'MFOX_DISPLAY_TRANSLATION_KEY',
                'value'       => false,
            ],
            'default_locale'          => [
                'config_name' => 'app.locale',
                'env_var'     => 'MFOX_SITE_LOCALE',
                'value'       => 'en',
            ],
            'default_timezone'        => [
                'env_var' => 'MFOX_SITE_TIMEZONE',
                'value'   => 'UTC',
            ],
            'default_country'         => [
                'config_name' => 'app.localize.country_iso',
                'env_var'     => 'MFOX_DEFAULT_COUNTRY_ISO',
                'value'       => 'US',
            ],
        ];
    }
}
