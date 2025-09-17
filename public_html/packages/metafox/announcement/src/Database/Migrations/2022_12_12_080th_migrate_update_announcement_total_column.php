<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumns('announcements', ['total_like', 'total_comment', 'total_reply'])) {
            Schema::table('announcements', function (Blueprint $table) {
                DbTableHelper::totalColumns($table, ['like', 'comment', 'reply']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumns('announcements', ['total_like', 'total_comment', 'total_reply'])) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn(['total_like', 'total_comment', 'total_reply']);
            });
        }
    }
};
