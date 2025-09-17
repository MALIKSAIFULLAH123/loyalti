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
        if (Schema::hasTable('music_playlists')) {
            Schema::table('music_playlists', function (Blueprint $table) {
                DbTableHelper::privacyColumn($table);
                DbTableHelper::totalColumns($table, ['attachment']);
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
            'music_playlists',
            ['total_attachment', DbTableHelper::PRIVACY_COLUMN]
        )) {
            Schema::table('music_playlists', function (Blueprint $table) {
                $table->dropColumn(['total_attachment', DbTableHelper::PRIVACY_COLUMN]);
            });
        }
    }
};
