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
        if (!Schema::hasTable('saved_search_items')) {
            Schema::create('saved_search_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('title');
                DbTableHelper::morphItemColumn($table);
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
        Schema::dropIfExists('saved_search_items');
    }
};
