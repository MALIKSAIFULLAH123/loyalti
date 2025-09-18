<?php

namespace MetaFox\Video\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Class PackageSeeder.
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
        $this->call(ServiceTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
    }
}
