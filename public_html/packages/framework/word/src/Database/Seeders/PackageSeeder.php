<?php

namespace MetaFox\Word\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Word\Word;

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
        Word::buildBlockWords();
    }
}
