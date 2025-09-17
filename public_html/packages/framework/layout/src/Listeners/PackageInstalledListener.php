<?php

namespace MetaFox\Layout\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Layout\Repositories\Eloquent\ThemeRepository;
use MetaFox\Layout\Repositories\Eloquent\VariantRepository;
use MetaFox\Platform\PackageManager;

class PackageInstalledListener
{
    public function handle($package)
    {
        $composerInfo = PackageManager::getComposerJson($package);
        $type = Arr::get($composerInfo, 'extra.metafox.type');

        if ($type !== 'theme') {
            return;
        }

        $themeRepository = resolve(ThemeRepository::class);
        $variantRepository = resolve(VariantRepository::class);

        $config = PackageManager::getConfig($package);

        foreach ($config['themes'] as $theme) {
            $themeRepository->getModel()
                ->newQuery()
                ->insertOrIgnore(array_merge([
                    'is_system'  => 0,
                    'is_active'  => 1,
                    'package_id' => $package,
                    'created_at' => now(),
                ], $theme));
        }

        foreach ($config['styles'] as $variant) {
            $variantRepository->getModel()->newQuery()
                ->insertOrIgnore(array_merge([
                    'is_system'  => 0,
                    'is_active'  => 1,
                    'package_id' => $package,
                    'created_at' => now(),
                ], $variant));
        }
    }
}