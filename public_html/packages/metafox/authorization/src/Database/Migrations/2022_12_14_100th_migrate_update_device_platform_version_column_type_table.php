<?php

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
        if (!Schema::hasTable('core_user_devices')) {
            return;
        }

        if (!Schema::hasColumn('core_user_devices', 'platform_version')) {
            return;
        }

        Schema::table('core_user_devices', function (Blueprint $table) {
            $table->dropColumn('platform_version');
        });

        Schema::table('core_user_devices', function (Blueprint $table) {
            $table->string('platform_version')->nullable();
        });

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Schema::dropIfExists('update_device_platform_version_column_type');
    }
};
