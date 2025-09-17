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
        if (!Schema::hasTable('stories')) {
            return;
        }

        Schema::table('stories', function (Blueprint $table) {
            $table->string('item_type', 30)->nullable(true);
            $table->unsignedBigInteger('item_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('stories')) {
            return;
        }

        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'item_id']);
        });
    }
};
