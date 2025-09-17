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
        if (!Schema::hasTable('gettingstarted_todo_lists')) {
            Schema::create('gettingstarted_todo_lists', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->unsignedInteger('ordering')->default(1);
                $table->string('resolution', 10)->default('web');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gettingstarted_todo_list_texts')) {
            Schema::create('gettingstarted_todo_list_texts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('todo_list_id')->unsigned();
                $table->mediumText('text');
                $table->mediumText('text_parsed');
                $table->string('locale', 10);
            });
        }

        if (!Schema::hasTable('gettingstarted_todo_list_images')) {
            Schema::create('gettingstarted_todo_list_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('todo_list_id');
                DbTableHelper::imageColumns($table);
                $table->unsignedInteger('ordering')->default(1);
            });
        }

        if (!Schema::hasTable('gettingstarted_todo_list_views')) {
            Schema::create('gettingstarted_todo_list_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('todo_list_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gettingstarted_user_first_login')) {
            Schema::create('gettingstarted_user_first_login', function (Blueprint $table) {
                $table->id();
                DbTableHelper::setupResourceColumns($table, true, false, false, false);
                $table->string('resolution', 10);
                $table->timestamps();
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
        Schema::dropIfExists('gettingstarted_todo_lists');
        Schema::dropIfExists('gettingstarted_todo_list_texts');
        Schema::dropIfExists('gettingstarted_todo_list_images');
        Schema::dropIfExists('gettingstarted_todo_list_views');
        Schema::dropIfExists('gettingstarted_user_first_login');
    }
};
