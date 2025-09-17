<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use MetaFox\Mobile\Models\AdMobConfig;

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
        if (!Schema::hasTable('ad_mob_configs')) {
            return;
        }

        DB::query()->from('ad_mob_configs')->where('frequency_capping', 'random')->update(['frequency_capping' => AdMobConfig::AD_MOB_FREQUENCY_RANDOM]);
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
