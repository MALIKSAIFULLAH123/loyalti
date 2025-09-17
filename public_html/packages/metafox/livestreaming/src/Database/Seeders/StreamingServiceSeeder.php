<?php

namespace MetaFox\LiveStreaming\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\LiveStreaming\Models\StreamingService;

/**
 * stub: packages/database/seeder-database.stub.
 */

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class StreamingServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (StreamingService::query()->exists()) {
            return;
        }
        StreamingService::query()->insert([
            'name'          => 'Mux',
            'driver'        => 'mux',
            'service_class' => \MetaFox\Mux\Support\Providers\Mux::class,
            'extra'         => json_encode([
                'url' => '/admincp/mux/setting/livestreaming',
            ]),
            'is_active'  => 1,
            'is_default' => 1,
        ]);
    }
}
