<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Models\StatsContent;

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
        if (!Schema::hasTable('core_stats_contents')) {
            return;
        }

        StatsContent::withoutEvents(function () {
            StatsContent::query()
                ->where('name', 'emoney_withdraw_request')
                ->whereIn('label', ['emoney::phrase.withdrawal_requests', 'ewallet::phrase.withdrawal_requests'])
                ->update([
                    'name'  => 'ewallet_withdraw_request',
                    'label' => 'ewallet::phrase.withdrawal_requests',
                ]);
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
