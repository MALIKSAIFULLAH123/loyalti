<?php

namespace MetaFox\InAppPurchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;

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
    public function run()
    {
        resolve(ProductRepositoryInterface::class)->initProducts();
    }
}
