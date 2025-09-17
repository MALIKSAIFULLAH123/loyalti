<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        $this->createChatPlusJobTable();
        $this->createChatPlusPermissionTable();
        $this->createChatPlusPermissionDataTable();
    }

    public function createChatPlusJobTable()
    {
        if (!Schema::hasTable('chatplus_jobs')) {
            Schema::create('chatplus_jobs', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->tinyInteger('is_sent')->default(0);
                $table->string('name')->nullable();
                $table->mediumText('data');
                $table->timestamps();
            });
        }
    }

    public function createChatPlusPermissionTable()
    {
        if (!Schema::hasTable('chatplus_permission')) {
            Schema::create('chatplus_permission', function (Blueprint $table) {
                $table->integerIncrements('setting_id')->unsigned();
                $table->string('perm_id');
                $table->tinyInteger('is_admin_setting')->default('0');
                $table->tinyInteger('is_hidden')->default('0');
                $table->string('name');
                $table->string('type_id');
                $table->integer('revision')->default('0');
                $table->integer('ordering')->default('0');
                $table->text('default_admin');
                $table->text('default_user');
                $table->text('default_guest');
                $table->text('default_staff');
                $table->text('default_moderator');
                $table->text('default_leader');
                $table->text('default_owner');
                $table->text('default_bot');
                $table->text('default_anonymous');
                $table->text('default_livechat_agent');
                $table->text('default_livechat_manager');
                $table->text('default_livechat_guest');
                $table->text('option_values');
                $table->timestamps();
            });
        }
    }

    public function createChatPlusPermissionDataTable()
    {
        if (!Schema::hasTable('chatplus_permission_data')) {
            Schema::create('chatplus_permission_data', function (Blueprint $table) {
                $table->string('user_group_id', 50)->nullable();
                $table->integer('setting_id');
                $table->text('value_actual');
                $table->index(['user_group_id', 'setting_id']);
                $table->timestamps();
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
        Schema::dropIfExists('chatplus_jobs');
        Schema::dropIfExists('chatplus_permissions');
        Schema::dropIfExists('chatplus_permission_data');
    }
};
