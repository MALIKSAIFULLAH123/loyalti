<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hashtag_tags')) {
            Schema::create('hashtag_tags', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('text');
                $table->string('tag_url');
                DbTableHelper::totalColumns($table, ['item']);
            });
        }

        if (!Schema::hasTable('hashtag_tag_data')) {
            Schema::create('hashtag_tag_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphItemColumn($table);
                $table->unsignedInteger('tag_id');
                $table->unique(['item_id', 'item_type', 'tag_id'], 'hashtag_tag_item_type');
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
        Schema::dropIfExists('hashtag_tags');
        Schema::dropIfExists('hashtag_tag_data');
    }
};
