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
        if (!Schema::hasTable('activity_snoozes')) {
            return;
        }

        Schema::table('activity_snoozes', function (Blueprint $table) {
            $columnsToRemove = ['is_system', 'is_snoozed', 'snooze_until'];

            if (Schema::hasColumns('activity_snoozes', $columnsToRemove)) {
                Schema::dropColumns('activity_snoozes', $columnsToRemove);
            }

            if (!Schema::hasColumn('activity_snoozes', 'snooze_until')) {
                $table->timestamp('snooze_until')->nullable()->default(null);
            }
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
