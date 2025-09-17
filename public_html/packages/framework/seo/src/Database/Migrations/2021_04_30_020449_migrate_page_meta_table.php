<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub.
 */

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *core_seo::metadata.
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('core_seo_meta')) {
            Schema::create('core_seo_meta', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                DbTableHelper::moduleColumn($table);
                $table->string('phrase_title')->nullable();
                $table->string('phrase_description')->nullable();
                $table->string('phrase_keywords')->nullable();
                $table->string('phrase_heading')->nullable();
                $table->string('secondary_menu')->nullable();
                $table->string('menu')->nullable();
                $table->string('resolution')->nullable()->default('web');
                $table->string('url')->nullable();
                $table->unsignedTinyInteger('custom_sharing_route')->default(0);
                $table->string('item_type')->nullable();
                $table->string('page_type')->nullable();
                $table->string('chunk')->nullable();
                $table->timestamps();

                $table->unique(['name'], 'core_seo_meta_uniq');
                $table->index(['url']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_seo_meta');
    }
};
