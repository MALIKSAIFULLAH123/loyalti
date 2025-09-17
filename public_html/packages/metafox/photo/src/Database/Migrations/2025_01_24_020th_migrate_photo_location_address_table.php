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
        $this->migratePhoto();
        $this->migratePhotoGroup();
    }

    protected function migratePhoto(): void
    {
        if (!Schema::hasTable('photos') || Schema::hasColumn('photos', 'location_address')) {
            return;
        }

        Schema::table('photos', function (Blueprint $table) {
            DbTableHelper::locationAddressColumn($table, 'location_address', 'location_name');
        });
    }

    protected function migratePhotoGroup(): void
    {
        if (!Schema::hasTable('photo_groups') || Schema::hasColumn('photo_groups', 'location_address')) {
            return;
        }

        Schema::table('photo_groups', function (Blueprint $table) {
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
        if (Schema::hasTable('photos') && Schema::hasColumn('photos', 'location_address')) {
            Schema::dropColumns('photos', 'location_address');
        }

        if (Schema::hasTable('photo_groups') && Schema::hasColumn('photo_groups', 'location_address')) {
            Schema::dropColumns('photo_groups', 'location_address');
        }
    }
};
