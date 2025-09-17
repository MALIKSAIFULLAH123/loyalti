<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Notification\Jobs\MigrateNotificationDataItem;

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
        if (Schema::hasColumns('notifications', ['data_item_id', 'data_item_type'])) {
            MigrateNotificationDataItem::dispatch();
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('data_item_id')->after('item_id')->nullable()->index();
            $table->string('data_item_type')->after('data_item_id')->nullable()->index();
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
        if (!Schema::hasTable('notifications')) {
            return;
        }

        if (Schema::hasColumns('notifications', ['data_item_id', 'data_item_type'])) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('data_item_id');
                $table->dropColumn('data_item_type');
            });
        }
    }
};
