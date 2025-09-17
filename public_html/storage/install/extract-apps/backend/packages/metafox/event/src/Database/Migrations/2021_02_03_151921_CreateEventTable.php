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
        //
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();

                DbTableHelper::moduleColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                $table->string('name');
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
                $table->unsignedTinyInteger('is_online')->default(0);
                $table->text('event_url')->nullable();

                DbTableHelper::viewColumn($table);
                DbTableHelper::imageColumns($table);
                DbTableHelper::locationColumn($table);
                $table->string('country_iso', 2)->nullable();
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::approvedColumn($table);
                DbTableHelper::tagsColumns($table);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::totalColumns(
                    $table,
                    ['like', 'share', 'view', 'feed', 'member', 'interested', 'attachment', 'pending_invite']
                );
                $table->unsignedTinyInteger('pending_mode')->default(0);

                $table->timestamps();
            });
        }

        DbTableHelper::categoryTable('event_categories', true);
        DbTableHelper::categoryDataTable('event_category_data');
        DbTableHelper::textTable('event_text');

        if (!Schema::hasTable('event_invites')) {
            Schema::create('event_invites', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('event_id')->index();
                $table->unsignedTinyInteger('status_id')->default(0);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table, true);
                $table->string('invited_email')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('event_members')) {
            Schema::create('event_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('event_id');
                DbTableHelper::morphUserColumn($table);
                $table->unsignedSmallInteger('rsvp_id')->default(0);
                $table->unsignedSmallInteger('role_id')->default(0);
                $table->unique(['user_id', 'event_id']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('event_invite_codes')) {
            Schema::create('event_invite_codes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('event_id');
                DbTableHelper::morphUserColumn($table);
                $table->string('code')->index();
                $table->unsignedSmallInteger('status')->default(1);
                $table->timestamps();
            });
        }

        DbTableHelper::streamTables('event');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_invite');
        Schema::dropIfExists('event_text');
        Schema::dropIfExists('event_members');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('event_category_data');
        DbTableHelper::dropStreamTables('event');
    }
};
