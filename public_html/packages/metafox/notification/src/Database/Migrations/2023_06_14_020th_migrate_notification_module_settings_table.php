<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (!Schema::hasColumn('notification_module_settings', 'module_id')) {
            return;
        }

        if (!Schema::hasColumn('notification_module_settings', 'notification_module_id')) {
            Schema::table('notification_module_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('notification_module_id')
                    ->default(0);
            });
        }

        DB::table('notification_module_settings')->update([
            'notification_module_id' => $this->migrateStatement(),
        ]);

        Schema::table('notification_module_settings', function (Blueprint $table) {
            $table->dropColumn('module_id');
            $table->renameColumn('notification_module_id', 'module_id');
            $table->index('module_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasColumn('notification_module_settings', 'module_id')) {
            return;
        }

        Schema::table('notification_module_settings', function (Blueprint $table) {
            $table->string('module_id', 500)
                ->nullable()
                ->change();
        });
    }

    private function migrateStatement()
    {
        return match (database_driver()) {
            'mysql' => DB::raw('CAST(module_id as UNSIGNED)'),
            default => DB::raw('CAST(module_id as BIGINT)'),
        };
    }
};
