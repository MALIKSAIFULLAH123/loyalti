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
        if (Schema::hasColumn('notification_types', 'require_module_id')) {
            return;
        }

        Schema::table('notification_types', function (Blueprint $table) {
            $table->string('require_module_id')->nullable()->index()->after('package_id');
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
        if (Schema::hasColumn('notification_types', 'require_module_id')) {
            Schema::dropColumns('notification_types', 'require_module_id');
        }
    }
};
