<?php

use MetaFox\Platform\Support\DbTableHelper;
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
        if (!Schema::hasTable('chat_subscriptions')) {
            return;
        }

        Schema::table('chat_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_subscriptions', 'is_seen_notification')) {
                $table->unsignedTinyInteger('is_seen_notification')
                    ->nullable()
                    ->default(0);
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
        if (!Schema::hasTable('chat_subscriptions')) {
            return;
        }

        Schema::table('chat_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('chat_subscriptions', 'is_seen_notification')) {
                $table->dropColumn('is_seen_notification');
            }
        });
    }
};
