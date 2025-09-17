<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        $this->migrateShare();
        $this->migrateActivityPost();
    }

    protected function migrateShare(): void
    {
        if (!Schema::hasTable('activity_shares') || Schema::hasColumn('activity_shares', 'location_address')) {
            return;
        }

        Schema::table('activity_shares', function (Blueprint $table) {
            DbTableHelper::locationAddressColumn($table, 'location_address', 'location_name');
        });
    }

    protected function migrateActivityPost(): void
    {
        if (!Schema::hasTable('activity_posts') || Schema::hasColumn('activity_posts', 'location_address')) {
            return;
        }

        Schema::table('activity_posts', function (Blueprint $table) {
            DbTableHelper::locationAddressColumn($table, 'location_address', 'location_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_posts') && Schema::hasColumn('activity_posts', 'location_address')) {
            Schema::dropColumns('activity_posts', ['location_address']);
        }

        if (Schema::hasTable('activity_shares') && Schema::hasColumn('activity_shares', 'location_address')) {
            Schema::dropColumns('activity_shares', ['location_address']);
        }
    }
};
