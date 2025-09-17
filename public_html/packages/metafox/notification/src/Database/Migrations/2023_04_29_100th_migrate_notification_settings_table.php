<?php

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
        if (!Schema::hasTable('notification_settings')) {
            return;
        }

        if (!Schema::hasColumn('notification_settings', 'channel')) {
            Schema::table('notification_settings', function (Blueprint $table) {
                $table->string('channel')->after('type_id')->default('database')->index();
            });
        }

        $this->migrate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('notification_settings')) {
            return;
        }

        if (Schema::hasColumn('notification_settings', 'channel')) {
            Schema::table('notification_settings', function (Blueprint $table) {
                $table->dropColumn('channel');
            });
        }
    }

    private function migrate()
    {
        // clean up orphaned data before migration
        DB::table('notification_settings')
            ->leftJoin('notification_type_channels', 'notification_settings.type_id', '=', 'notification_type_channels.id')
            ->whereNull('notification_type_channels.id')
            ->delete();

        return match (database_driver()) {
            'mysql' => $this->migrateMysql(),
            default => $this->migratePostgres(),
        };
    }

    private function migrateMysql()
    {
        return DB::statement(
            'UPDATE notification_settings
            JOIN notification_type_channels
            ON notification_settings.type_id = notification_type_channels.id
            SET notification_settings.type_id = notification_type_channels.type_id,
            notification_settings.channel = notification_type_channels.channel'
        );
    }

    private function migratePostgres()
    {
        return DB::statement(
            'UPDATE notification_settings
            SET type_id = notification_type_channels.type_id, channel = notification_type_channels.channel
            FROM notification_type_channels
            WHERE notification_settings.type_id = notification_type_channels.id'
        );
    }
};
