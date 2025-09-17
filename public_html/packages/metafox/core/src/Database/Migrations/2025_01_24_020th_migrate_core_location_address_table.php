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
        if (!Schema::hasTable('core_links') || Schema::hasColumn('core_links', 'location_address')) {
            return;
        }

        Schema::table('core_links', function (Blueprint $table) {
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
        if (!Schema::hasTable('core_links') || !Schema::hasColumn('core_links', 'location_address')) {
            return;
        }

        Schema::dropColumns('core_links', 'location_address');
    }
};
