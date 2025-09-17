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
            if (!Schema::hasColumn('chat_subscriptions', 'is_showed')) {
                $table->unsignedTinyInteger('is_showed')->default(0);
            }

            if (!Schema::hasColumn('chat_subscriptions', 'is_deleted')) {
                $table->unsignedTinyInteger('is_deleted')->default(0);
            }

            if (!Schema::hasColumn('chat_subscriptions', 'rejoin_at')) {
                $table->timestamp('rejoin_at')->nullable()->default(null);
            }

            if (!Schema::hasColumn('chat_subscriptions', 'total_unseen')) {
                $table->bigInteger('total_unseen')->default(0);
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
            if (Schema::hasColumn('chat_subscriptions', 'is_showed')) {
                $table->dropColumn('is_showed');
            }

            if (Schema::hasColumn('chat_subscriptions', 'is_deleted')) {
                $table->dropColumn('is_deleted');
            }

            if (Schema::hasColumn('chat_subscriptions', 'rejoin_at')) {
                $table->dropColumn('rejoin_at');
            }

            if (Schema::hasColumn('chat_subscriptions', 'total_unseen')) {
                $table->dropColumn('total_unseen');
            }
        });
    }
};
