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
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'total_attachment')) {
                DbTableHelper::totalColumns($table, ['attachment']);
            }

            if (!Schema::hasColumn('chat_messages', 'extra')) {
                $table->json('extra')->nullable()->default(null);
            }

            if (!Schema::hasColumn('chat_messages', 'reactions')) {
                $table->json('reactions')->nullable()->default(null);
            }

            if (!Schema::hasColumn('chat_messages', 'seen_users')) {
                $table->json('seen_users')->nullable()->default(null);
            }

            if (Schema::hasColumn('chat_messages', 'message')) {
                $table->mediumText('message')->nullable()->change();
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
        if (!Schema::hasTable('chat_messages')) {
            return;
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'total_attachment')) {
                $table->dropColumn('total_attachment');
            }

            if (Schema::hasColumn('chat_messages', 'extra')) {
                $table->dropColumn('extra');
            }

            if (Schema::hasColumn('chat_messages', 'reactions')) {
                $table->dropColumn('reactions');
            }

            if (Schema::hasColumn('chat_messages', 'seen_users')) {
                $table->dropColumn('seen_users');
            }
        });
    }
};
