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
        if (Schema::hasTable('music_albums')) {
            Schema::table('music_albums', function (Blueprint $table) {
                DbTableHelper::totalColumns($table, ['attachment', 'view', 'share']);
            });
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
        if (Schema::hasColumns(
            'music_albums',
            ['total_attachment', 'total_view', 'total_share']
        )) {
            Schema::table('music_albums', function (Blueprint $table) {
                $table->dropColumn(['total_attachment', 'total_view', 'total_share']);
            });
        }
    }
};
