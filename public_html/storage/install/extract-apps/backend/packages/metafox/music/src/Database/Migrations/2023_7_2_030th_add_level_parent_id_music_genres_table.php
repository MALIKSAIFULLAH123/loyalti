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
        if (Schema::hasTable('music_genres')) {
            Schema::table('music_genres', function (Blueprint $table) {
                $table->unsignedInteger('level')
                    ->default(1);
                $table->unsignedInteger('parent_id')
                    ->nullable();
                $table->string('name_url')
                    ->nullable()->index();
                DbTableHelper::totalColumns($table, ['item']);
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
        if (Schema::hasColumns('music_genres', ['level', 'parent_id', 'name_url'])) {
            Schema::table('music_genres', function (Blueprint $table) {
                $table->dropColumn(['level', 'parent_id', 'name_url', 'total_item']);
            });
        }
    }
};
