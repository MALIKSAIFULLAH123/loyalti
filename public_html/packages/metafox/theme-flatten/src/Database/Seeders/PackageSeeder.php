<?php

namespace Metafox\ThemeFlatten\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Layout\Repositories\ThemeRepositoryInterface;
use MetaFox\Layout\Repositories\VariantRepositoryInterface;

/**
 * stub: packages/database/seeder-database.stub
 */

/**
 * Class PackageSeeder
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function run(ThemeRepositoryInterface $themeRepository, VariantRepositoryInterface $variantRepository)
    {
        $packageId = 'metafox/theme-flatten';

        $config = app('files')
            ->getRequire(base_path('packages/metafox/theme-flatten/config/config.php'));

        foreach ($config['themes'] as $theme) {
            $themeRepository->getModel()
                ->newQuery()
                ->insertOrIgnore(array_merge([
                    'is_system'  => 0,
                    'is_active'  => 1,
                    'package_id' => $packageId,
                    'created_at' => now(),
                ], $theme));
        }

        foreach ($config['styles'] as $variant) {
            $variantRepository->getModel()->newQuery()
                ->insertOrIgnore(array_merge([
                        'is_system'  => 0,
                        'is_active'  => 1,
                        'package_id' => $packageId,
                        'created_at' => now(),
                ], $variant));
        }
    }
}
