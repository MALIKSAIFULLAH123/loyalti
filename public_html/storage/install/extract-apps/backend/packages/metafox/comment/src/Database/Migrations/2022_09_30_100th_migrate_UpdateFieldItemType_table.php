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
        if (Schema::hasTable('comment_histories')) {
            Schema::table('comment_histories', function (Blueprint $table) {
                $table->dropColumn(['item_type']);
            });
        }

        if (Schema::hasTable('comment_histories')) {
            Schema::table('comment_histories', function (Blueprint $table) {
                $table->enum('item_type', ['sticker', 'storage_file', 'link'])
                    ->default('storage_file');
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
        if (Schema::hasTable('comment_histories')) {
            Schema::table('comment_histories', function (Blueprint $table) {
                $table->dropColumn(['item_type']);
            });
        }
    }
};
