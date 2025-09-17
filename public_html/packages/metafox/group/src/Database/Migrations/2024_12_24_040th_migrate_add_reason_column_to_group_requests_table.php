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
        if (!Schema::hasTable('group_requests')) {
            return;
        }

        if (!Schema::hasColumn('group_requests', 'reason')) {
            Schema::table('group_requests', function (Blueprint $table) {
                $table->text('reason')->nullable();
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
        if (!Schema::hasTable('group_requests')) {
            return;
        }

        if (Schema::hasColumn('group_requests', 'reason')) {
            Schema::table('group_requests', function (Blueprint $table) {
                $table->dropColumn(['reason']);
            });
        }
    }
};
