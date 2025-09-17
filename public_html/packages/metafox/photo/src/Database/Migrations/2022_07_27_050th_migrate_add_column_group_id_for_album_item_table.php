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
        if (Schema::hasTable('photo_album_item')) {
            Schema::table('photo_album_item', function (Blueprint $table) {
                $table->unsignedBigInteger('group_id');
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
        if (Schema::hasColumn('photo_album_item', 'group_id')) {
            Schema::table('photo_album_item', function (Blueprint $table) {
                $table->dropColumn(['group_id']);
            });
        }
    }
};
