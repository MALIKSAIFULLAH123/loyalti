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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type');

                DbTableHelper::morphColumn($table, 'notifiable');
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::morphUserColumn($table);

                $table->mediumText('data')->nullable();

                $table->timestamp('read_at')
                    ->nullable();

                $table->timestamp('notified_at')
                    ->nullable();

                $table->boolean('is_request')
                    ->default(false);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_tokens')) {
            Schema::create('notification_tokens', function (Blueprint $table) {
                $table->string('id', 255)->primary();

                DbTableHelper::morphUserColumn($table);

                $table->string('token');

                $table->string('environment')
                    ->default('production');

                $table->string('platform', 100)
                    ->default('ios');

                $table->string('token_source', 100)
                    ->default('firebase');

                $table->boolean('is_active')
                    ->default(1);
            });
        }

        if (!Schema::hasTable('notification_types')) {
            Schema::create('notification_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type')->unique();
                $table->string('handler')->nullable();
                $table->string('title');
                DbTableHelper::moduleColumn($table);
                $table->string('channels')->default('[database]');

                $table->unsignedInteger('can_edit')->default(1);
                $table->unsignedInteger('is_request')->default(0);
                $table->unsignedInteger('is_active')->default(1);
                $table->unsignedInteger('is_system')->default(1);
                $table->unsignedInteger('ordering')->default(1);
            });
        }

        if (!Schema::hasTable('notification_channels')) {
            Schema::create('notification_channels', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('title');
                $table->unsignedInteger('is_system')->default(0);
                $table->unsignedInteger('is_active')->default(1);
            });
        }

        if (!Schema::hasTable('notification_settings')) {
            Schema::create('notification_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->unsignedInteger('type_id');
                $table->mediumText('user_value')->nullable(true);
            });
        }

        if (!Schema::hasTable('notification_webpush_subscriptions')) {
            Schema::create('notification_webpush_subscriptions', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphColumn($table, 'subscribable', false, 'notification_webpush_subscriptions_user');
                $table->string('endpoint', 500)->unique();
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->string('content_encoding')->nullable();
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
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_tokens');
        Schema::dropIfExists('notification_types');
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('notification_webpush_subscriptions');
    }
};
