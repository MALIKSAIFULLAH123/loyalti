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
        if (Schema::hasColumn('core_user_devices', 'token_id')) {
            return;
        }

        Schema::table('core_user_devices', function (Blueprint $table) {
            $table->string('token_id', 100)->nullable()->after('device_uid');
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
        Schema::table('core_user_devices', function (Blueprint $table) {
            if (Schema::hasColumn('core_user_devices', 'token_id')) {
                $table->dropColumn('token_id');
            }
        });
    }
};
