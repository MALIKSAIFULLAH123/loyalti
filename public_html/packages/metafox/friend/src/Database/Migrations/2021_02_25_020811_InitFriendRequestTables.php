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
        if (!Schema::hasTable('friends')) {
            Schema::create('friends', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                $table->index(['user_id', 'owner_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('friend_lists')) {
            Schema::create('friend_lists', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                DbTableHelper::morphUserColumn($table);
                $table->string('name');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('friend_list_data')) {
            Schema::create('friend_list_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('list_id');

                DbTableHelper::morphUserColumn($table);

                $table->unsignedInteger('ordering')
                    ->default(0);
            });
        }

        if (!Schema::hasTable('friend_requests')) {
            Schema::create('friend_requests', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                DbTableHelper::morphOwnerColumn($table);

                $table->unsignedTinyInteger('status_id')
                    ->default(0);

                $table->unsignedTinyInteger('is_deny')
                    ->default(0);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('friend_tag_friends')) {
            Schema::create('friend_tag_friends', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->float('px', 6, 3)->default(0);
                $table->float('py', 6, 3)->default(0);
                $table->tinyInteger('is_mention')->default(0);
                DbTableHelper::feedContentColumn($table);
            });
        }

        if (!Schema::hasTable('friend_suggestion_ignore')) {
            Schema::create('friend_suggestion_ignore', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
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
        Schema::dropIfExists('friend_list_data');
        Schema::dropIfExists('friend_lists');
        Schema::dropIfExists('friends');
        Schema::dropIfExists('friend_requests');
        Schema::dropIfExists('friend_tag_friends');
        Schema::dropIfExists('friend_suggestion_ignore');
    }
};
