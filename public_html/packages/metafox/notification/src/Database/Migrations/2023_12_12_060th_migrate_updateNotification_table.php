<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Notification\Models\Notification;

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
            Notification::query()
                ->where('item_type', 'photo_set')
                ->orderBy('id')
                ->update([
                    'data_item_id'   => null,
                    'data_item_type' => 'feed',
                ]);
        }

        // to do here
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
