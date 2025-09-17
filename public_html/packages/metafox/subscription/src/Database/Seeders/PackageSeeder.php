<?php

namespace MetaFox\Subscription\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Subscription\Models\SubscriptionCancelReason;
use MetaFox\Subscription\Support\Helper;

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
        $this->initDefaultReason();
    }

    protected function initDefaultReason(): void
    {
        if(SubscriptionCancelReason::query()->exists()){
            return;
        }

        $count = SubscriptionCancelReason::where([
            'is_default' => true,
        ])->count();

        if (!$count) {
            $reason = new SubscriptionCancelReason();

            $reason->fill([
                'title'      => 'Other reasons',
                'status'     => Helper::STATUS_ACTIVE,
                'is_default' => true,
                'ordering'   => 1,
            ]);

            $reason->save();
        }
    }
}
