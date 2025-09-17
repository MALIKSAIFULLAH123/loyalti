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
        if (!Schema::hasTable('saved_items')) {
            Schema::create('saved_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphItemColumn($table);
                $table->unsignedTinyInteger('is_opened')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saved_lists')) {
            Schema::create('saved_lists', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->string('name');
                $table->unsignedBigInteger('saved_id')->default(0);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saved_list_data')) {
            Schema::create('saved_list_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('list_id');
                $table->unsignedBigInteger('saved_id');
            });
        }

        if (!Schema::hasTable('saved_aggregations')) {
            Schema::create('saved_aggregations', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->string('item_type');
                $table->unsignedInteger('total_saved')
                    ->default(1);

                $table->unique(['user_id', 'user_type', 'item_type']);
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
        Schema::dropIfExists('saved_lists');
        Schema::dropIfExists('saved_list_data');
        Schema::dropIfExists('saved_items');
        Schema::dropIfExists('saved_aggregations');
    }
};
