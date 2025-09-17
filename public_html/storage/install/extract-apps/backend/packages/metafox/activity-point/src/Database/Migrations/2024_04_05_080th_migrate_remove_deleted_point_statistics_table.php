<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\ActivityPoint\Models\PointStatistic;

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
        if (!Schema::hasTable('apt_statistics')) {
            return;
        }

        PointStatistic::withoutEvents(function () {
            $obsoleteStatistics = PointStatistic::query()->whereDoesntHave('activeUserEntity')->get()->collect();

            $obsoleteStatistics->each(function ($item) {
                if (!$item instanceof PointStatistic) {
                    return false;
                }

                $item->delete();
            });
        });
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
