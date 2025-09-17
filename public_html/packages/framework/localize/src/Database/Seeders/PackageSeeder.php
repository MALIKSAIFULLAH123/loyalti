<?php

namespace MetaFox\Localize\Database\Seeders;

use Illuminate\Database\Seeder;

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
    public function run(): void
    {
        $this->call(LanguageTableSeeder::class);
        $this->call(CountryTablesSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        $this->call(TimezoneSeeder::class);
    }
}
