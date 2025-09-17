<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bgs_collections')) {
            Schema::create('bgs_collections', function (Blueprint $table) {
                $table->integerIncrements('id');

                $table->string('title', 100);

                $table->unsignedInteger('main_background_id')
                    ->default(0);

                $table->unsignedTinyInteger('is_active')
                    ->default(1);

                $table->unsignedTinyInteger('is_default')
                    ->default(0);

                $table->unsignedTinyInteger('view_only')
                    ->default(0);

                $table->unsignedTinyInteger('is_deleted')
                    ->default(0);

                $table->unsignedInteger('total_background')
                    ->default(0);

                $table->timestamp('created_at')
                    ->useCurrent();

                $table->index('main_background_id');
            });
        }

        if (!Schema::hasTable('bgs_backgrounds')) {
            Schema::create('bgs_backgrounds', function (Blueprint $table) {
                $table->integerIncrements('id');

                $table->unsignedInteger('collection_id');

                DbTableHelper::imageColumns($table);

                $table->string('icon_path')->nullable();
                $table->string('image_path')->nullable();
                $table->string('server_id')->nullable();
                $table->unsignedTinyInteger('view_only')->default(0);

                $table->unsignedTinyInteger('is_deleted')->default(0);

                $table->unsignedInteger('ordering')->default(0);

                $table->timestamp('created_at')
                    ->useCurrent();

                $table->index(['collection_id', 'ordering']);
            });
        }

        if (!Schema::hasTable('bgs_recent_used')) {
            Schema::create('bgs_recent_used', function (Blueprint $table) {
                $table->integerIncrements('id');

                DbTableHelper::morphUserColumn($table);

                $table->unsignedInteger('background_id');

                $table->timestamp('created_at')
                    ->useCurrent();

                $table->unique(['user_id', 'background_id']);
            });
        }

        if (!Schema::hasTable('bgs_status_background')) {
            Schema::create('bgs_status_background', function (Blueprint $table) {
                $table->integerIncrements('id');

                DbTableHelper::morphItemColumn($table);
                DbTableHelper::morphUserColumn($table);

                $table->unsignedInteger('background_id');

                $table->unsignedTinyInteger('is_active')
                    ->default(1);
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
        Schema::dropIfExists('bgs_collections');
        Schema::dropIfExists('bgs_backgrounds');
        Schema::dropIfExists('bgs_recent_used');
        Schema::dropIfExists('bgs_status_background');
    }
};
