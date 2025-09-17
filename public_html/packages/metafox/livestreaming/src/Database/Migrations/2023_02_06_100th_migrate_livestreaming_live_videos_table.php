<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\DbTableHelper;

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
        $this->createLiveVideoTable();
        $this->createNotificationSettingTable();
        $this->createPlaybackDataTable();
        $this->createUserStreamKeyTable();
        DbTableHelper::textTable('livestreaming_text');
        DbTableHelper::createTagDataTable('livestreaming_tag_data');
        DbTableHelper::streamTables('livestreaming');
        $this->createLiveStreamingServiceTable();
    }

    private function createLiveVideoTable()
    {
        if (Schema::hasTable('livestreaming_live_videos')) {
            return;
        }
        Schema::create('livestreaming_live_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
            $table->string('stream_key', 255);
            $table->string('duration')->nullable();
            $table->string('asset_id')->nullable();
            $table->string('live_stream_id')->nullable();
            $table->string('live_type', 10)->default('mobile');
            $table->unsignedTinyInteger('is_streaming')->default(0);
            $table->unsignedTinyInteger('is_landscape')->default(1);
            DbTableHelper::setupResourceColumns($table, true, true, true, true);
            DbTableHelper::featuredColumn($table);
            DbTableHelper::sponsorColumn($table);
            DbTableHelper::tagsColumns($table);
            DbTableHelper::totalColumns($table, ['comment', 'reply', 'like', 'share', 'view', 'attachment']);
            DbTableHelper::imageColumns($table);
            DbTableHelper::approvedColumn($table);
            DbTableHelper::locationColumn($table);
            $table->unsignedTinyInteger('view_id')->default(0);
            $table->string('last_ping')->nullable();
            $table->unsignedInteger('total_viewer')->default(0);
            $table->unsignedTinyInteger('allow_feed')->default(0);
            $table->text('tagged_friends')->nullable();
            $table->timestamps();
        });
    }

    private function createPlaybackDataTable()
    {
        if (Schema::hasTable('livestreaming_playback_data')) {
            return;
        }

        Schema::create('livestreaming_playback_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('live_id');
            $table->string('playback_id');
            $table->tinyInteger('privacy')->default(0);
            $table->timestamps();
        });
    }

    private function createUserStreamKeyTable()
    {
        if (Schema::hasTable('livestreaming_user_stream_key')) {
            return;
        }

        Schema::create('livestreaming_user_stream_key', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('asset_id')->nullable();
            $table->string('stream_key');
            $table->string('live_stream_id');
            $table->string('playback_ids')->nullable();
            $table->unsignedTinyInteger('is_streaming')->default(0);
            $table->unsignedInteger('connected_from')->default(0);
            $table->timestamps();
        });
    }

    private function createNotificationSettingTable()
    {
        if (Schema::hasTable('livestreaming_notification_setting')) {
            return;
        }
        Schema::create('livestreaming_notification_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::setupResourceColumns($table, true, true, false, false);
            $table->timestamps();
        });
    }

    private function createLiveStreamingServiceTable(): void
    {
        if (Schema::hasTable('livestreaming_service')) {
            return;
        }

        // to do here
        Schema::create('livestreaming_service', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('driver');
            $table->string('name');
            $table->unsignedTinyInteger('is_default')->default(0);
            $table->unsignedTinyInteger('is_active')->default(0);
            $table->string('service_class');
            $table->text('extra')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('livestreaming_live_videos');
        Schema::dropIfExists('livestreaming_text');
        Schema::dropIfExists('livestreaming_tag_data');
        Schema::dropIfExists('livestreaming_playback_data');
        Schema::dropIfExists('livestreaming_user_stream_key');
        Schema::dropIfExists('livestreaming_notification_setting');
        Schema::dropIfExists('livestreaming_service');
        DbTableHelper::dropStreamTables('livestreaming');
    }
};
