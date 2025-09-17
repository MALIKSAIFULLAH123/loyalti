<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('layout_variants')) {
            Schema::create('layout_variants', function (Blueprint $table) {
                $table->id('id');
                $table->string('theme_id');
                $table->string('variant_id');
                $table->string('title');
                $table->unsignedTinyInteger('is_active')->default(0);
                $table->unsignedTinyInteger('is_system')->default(0);
                $table->string('thumb_url')->nullable();
                DbTableHelper::imageColumns($table, 'thumb_id');
                DbTableHelper::imageColumns($table, 'dark_thumb_id');
                DbTableHelper::moduleColumn($table);

                $table->unique(['variant_id']);

                $table->timestamps();
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
        Schema::dropIfExists('layout_variants');
    }
};
