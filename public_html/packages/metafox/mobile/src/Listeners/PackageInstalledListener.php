<?php

namespace MetaFox\Mobile\Listeners;

use MetaFox\Mobile\Models\AdMobPage;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class PackageInstalledListener
{
    public function handle(string $package): void
    {
        $listener = PackageManager::getListener($package);

        if (!$listener) {
            return;
        }
        $this->makeAdMobPage($package, $listener);
    }

    protected function makeAdMobPage(string $packageId, BasePackageSettingListener $listener): void
    {
        $pages = $listener->getAdMobPages();
        if (!is_array($pages)) {
            return;
        }

        $data = [];
        foreach ($pages as $page) {
            $data[] = array_merge($page, [
                'package_id' => $packageId,
                'module_id'  => PackageManager::getAlias($packageId),
            ]);
        }

        AdMobPage::query()->upsert($data, ['path', 'package_id'], ['name', 'module_id']);
    }
}
