<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Activity\Support\Support;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @codeCoverageIgnore
 * @ignore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('activity_feeds')) {
            Schema::create('activity_feeds', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::privacyColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::morphItemWithTypeColumn($table);

                $table->unsignedBigInteger('feed_reference')
                    ->default(0);

                $table->unsignedBigInteger('parent_feed_id')
                    ->default(0);

                $table->string('parent_module_id', 75)
                    ->nullable();

                $table->enum('status', Support::getItemStatuses())
                    ->index()
                    ->default(MetaFoxConstant::ITEM_STATUS_APPROVED);

                DbTableHelper::feedContentColumn($table);

                DbTableHelper::totalColumns($table, ['view', 'like', 'comment', 'reply', 'share']);

                $table->unsignedTinyInteger('is_sponsor')
                    ->default(0)
                    ->index();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_streams')) {
            Schema::create('activity_streams', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('feed_id')->index();

                $table->unsignedBigInteger('user_id');

                DbTableHelper::morphColumnIndexedType($table, 'owner');

                DbTableHelper::privacyIdColumn($table);

                DbTableHelper::morphItemColumn($table);

                $table->unsignedBigInteger('status')->default(0);

                $table->timestamp('created_at')
                    ->useCurrent()->index();

                $table->timestamp('updated_at')
                    ->useCurrent()->index();

                $table->index(['feed_id', 'updated_at']);
            });
        }

        if (!Schema::hasTable('activity_privacy_members')) {
            // privacy_uid=4, (user,1), 'STAFF'
            Schema::create('activity_privacy_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::privacyIdColumn($table);
                $table->unsignedBigInteger('user_id')->index();

                $table->unique(['privacy_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('activity_subscriptions')) {
            Schema::create('activity_subscriptions', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->integer('owner_id');

                $table->integer('user_id')->index();

                $table->boolean('is_active')->default(true);

                $table->index(['user_id', 'owner_id'], 'user_subscription');
                $table->index(['user_id', 'owner_id', 'is_active'], 'user_active_subscription');
            });
        }

        DbTableHelper::createTagDataTable('activity_tag_data');

        // Create snooze + hidden.
        $this->activitySnoozeHidden();

        // Create activity type.
        $this->activityType();

        // Create table resource activity_post.
        $this->activityPost();

        // Create table resource activity_attachment.
        $this->activityAttachment();

        // Create table share.
        $this->activityShare();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_tag_data');
        Schema::dropIfExists('activity_hidden');
        Schema::dropIfExists('activity_data');
        Schema::dropIfExists('activity_members');
        Schema::dropIfExists('activity_snoozes');
        Schema::dropIfExists('activity_subscriptions');
        Schema::dropIfExists('activity_privacy_members');
        Schema::dropIfExists('activity_privacy');
        Schema::dropIfExists('activity_streams');
        Schema::dropIfExists('activity_feeds');
        Schema::dropIfExists('activity_posts');
        Schema::dropIfExists('activity_types');

        Schema::dropIfExists('activity_attachment_data');
        Schema::dropIfExists('activity_attachments');

        Schema::dropIfExists('activity_shares');
    }

    private function activitySnoozeHidden(): void
    {
        if (!Schema::hasTable('activity_snoozes')) {
            Schema::create('activity_snoozes', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, true, false, false);

                $table->timestamp('snooze_until')
                    ->useCurrent()->nullable()->default(null);

                $table->tinyInteger('is_snooze_forever')
                    ->default(0);

                $table->unique(['user_id', 'owner_id']);

                $table->index(['user_id', 'owner_id', 'is_snooze_forever'], 'hide_all_user');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_hidden')) {
            Schema::create('activity_hidden', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, false, false, false);

                $table->unsignedBigInteger('feed_id')->index();

                $table->unique(['user_id', 'feed_id']);

                $table->timestamps();
            });
        }
    }

    private function activityType(): void
    {
        if (!Schema::hasTable('activity_types')) {
            Schema::create('activity_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type')->unique();
                DbTableHelper::moduleColumn($table);
                DbTableHelper::entityTypeColumn($table);
                $table->string('title');
                $table->mediumText('description')->nullable();
                $table->mediumText('value_actual')->nullable();
                $table->mediumText('value_default')->nullable();
                $table->unsignedInteger('is_active')->default(1);
                $table->unsignedInteger('is_system')->default(1);
                $table->mediumText('params')->nullable();
            });
        }
    }

    private function activityPost(): void
    {
        if (!Schema::hasTable('activity_posts')) {
            Schema::create('activity_posts', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, true, true, false);
                DbTableHelper::approvedColumn($table);
                DbTableHelper::totalColumns($table, ['like', 'comment', 'reply', 'share']);

                DbTableHelper::locationColumn($table);
                DbTableHelper::feedContentColumn($table);

                $table->unsignedBigInteger('status_background_id')->default(0);

                $table->timestamps();
            });
        }
    }

    private function activityAttachment(): void
    {
        if (!Schema::hasTable('activity_attachments')) {
            Schema::create('activity_attachments', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, true, true, false);
                DbTableHelper::totalColumns($table, ['like', 'comment', 'reply', 'share', 'item']);
                DbTableHelper::feedContentColumn($table);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_attachment_data')) {
            Schema::create('activity_attachment_data', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('attachment_id')->index();

                DbTableHelper::morphItemColumn($table);
            });
        }
    }

    private function activityShare(): void
    {
        if (!Schema::hasTable('activity_shares')) {
            Schema::create('activity_shares', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::privacyColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::approvedColumn($table);
                $table->unsignedBigInteger('parent_feed_id')
                    ->default(0);

                $table->string('parent_module_id', 75)
                    ->nullable();

                DbTableHelper::feedContentColumn($table);

                DbTableHelper::totalColumns($table, ['like', 'comment', 'reply', 'share', 'view']);

                DbTableHelper::locationColumn($table);

                $table->timestamps();
            });
        }
    }
};
