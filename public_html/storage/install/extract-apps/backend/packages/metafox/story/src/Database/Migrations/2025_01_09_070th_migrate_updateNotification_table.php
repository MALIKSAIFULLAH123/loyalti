<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Notification\Models\Notification;
use MetaFox\Story\Models\StoryReaction;

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
                ->where('item_type', StoryReaction::ENTITY_TYPE)
                ->orWhere('data_item_type', StoryReaction::ENTITY_TYPE)
                ->orderBy('id')
                ->update([
                    'data_item_id'   => null,
                    'data_item_type' => 'like',
                ]);
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {}
};
