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
        if (!Schema::hasTable('friend_tag_friends')) {
            return;
        }

        if (Schema::hasColumn('friend_tag_friends', 'send_notification')) {
            return;
        }

        Schema::table('friend_tag_friends', function (Blueprint $table) {
            $table->boolean('send_notification')
                ->nullable(false)
                ->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('friend_tag_friends')) {
            return;
        }

        if (!Schema::hasColumn('friend_tag_friends', 'send_notification')) {
            return;
        }

        Schema::table('friend_tag_friends', function (Blueprint $table) {
            $table->dropColumn('send_notification');
        });
    }
};
