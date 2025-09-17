<?php

namespace MetaFox\Backup\Database\Seeders;

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
    public function run()
    {
        app('storage')
            ->tap('backup', [
                'label'     => 'Backup & Restore',
                'target'    => 'local',
                'is_system' => true,
            ]);
    }
}
