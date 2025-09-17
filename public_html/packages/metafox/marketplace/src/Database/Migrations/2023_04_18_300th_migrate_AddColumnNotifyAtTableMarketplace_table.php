<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Platform\Facades\Settings;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $column = 'notify_at';

        $tableName = 'marketplace_listings';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->bigInteger($column)
                ->default(0);
        });

        /** @var ListingRepository $repository */
        $repository   = resolve(ListingRepository::class);

        $expiredDays = (int) Settings::get('marketplace.days_to_expire', 30);

        $query = $repository->getModel()->newQuery()
            ->where('start_expired_at', '>', 0);

        if (0 == $expiredDays) {
            $query->update(['start_expired_at' => 0]);

            return;
        }

        foreach ($query->get() as $marketplace) {
            $newTimestamp = \Illuminate\Support\Carbon::parse($marketplace->start_expired_at)
                ->addDays($expiredDays)
                ->timestamp;

            Listing::withoutEvents(function () use ($marketplace, $newTimestamp) {
                $marketplace->update([
                    'start_expired_at' => $newTimestamp,
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
