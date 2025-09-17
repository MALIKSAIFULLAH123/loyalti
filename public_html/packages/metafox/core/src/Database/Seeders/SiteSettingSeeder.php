<?php

namespace MetaFox\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;

/**
 * Class SiteSettingSeeder.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @ignore
 * @codeCoverageIgnore
 */
class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->siteSettingSeeder();
    }

    private function siteSettingSeeder(): void
    {
        $response = PackageManager::discoverSettings('getSiteSettings');

        if (!is_array($response) || empty($response)) {
            return;
        }

        foreach ($response as $moduleId => $settings) {
            if (empty($settings) || !is_string($moduleId)) {
                continue;
            }
            Settings::setupPackageSettings($moduleId, $settings);
        }
    }
}
