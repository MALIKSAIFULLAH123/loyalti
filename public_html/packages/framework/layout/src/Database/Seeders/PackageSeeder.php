<?php

namespace MetaFox\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Layout\Repositories\VariantRepositoryInterface;
use MetaFox\Layout\Repositories\ThemeRepositoryInterface;

/**
 * stub: packages/database/seeder-database.stub.
 */

/**
 * Class PackageSeeder.
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
     */
    public function run(ThemeRepositoryInterface $themeRepository, VariantRepositoryInterface $variantRepository)
    {
        $config = app('files')
            ->getRequire(base_path('packages/framework/layout/config/config.php'));

        foreach ($config['themes'] as $theme) {
            $themeRepository->getModel()
                ->newQuery()
                ->insertOrIgnore($theme);
        }

        foreach ($config['styles'] as $variant) {
            $variantRepository->getModel()->newQuery()
                ->insertOrIgnore($variant);
        }
    }
}
