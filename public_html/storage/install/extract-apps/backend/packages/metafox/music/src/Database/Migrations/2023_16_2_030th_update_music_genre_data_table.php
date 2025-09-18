<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (Schema::hasTable('music_genre_data')) {
            Schema::table('music_genre_data', function (Blueprint $table) {
                $table->string('item_type')->default('music_song');
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
            'music_genre_data',
            ['item_type']
        )) {
            Schema::table('music_genre_data', function (Blueprint $table) {
                $table->dropColumn(['item_type']);
            });
        }
    }
};
