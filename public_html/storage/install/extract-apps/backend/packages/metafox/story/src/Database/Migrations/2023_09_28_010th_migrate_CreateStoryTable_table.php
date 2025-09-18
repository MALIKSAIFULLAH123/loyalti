<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        $this->createStorySetsTable();
        $this->createStoryTable();
        $this->createStoryCollectionsTable();
        $this->createStoryBackgroundsTable();
        $this->createStoryViewsTable();
        $this->createStoryReactionsTable();
        $this->createStoryReactionsDataTable();

        DbTableHelper::textTable('story_text');
        DbTableHelper::createTagDataTable('story_tag_data');
        DbTableHelper::streamTables('story');
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('story_sets');
        Schema::dropIfExists('stories');
        Schema::dropIfExists('story_background_set');
        Schema::dropIfExists('story_views');
        Schema::dropIfExists('story_backgrounds');
        Schema::dropIfExists('story_text');
        Schema::dropIfExists('story_tag_data');
        Schema::dropIfExists('story_reactions');
        Schema::dropIfExists('story_reaction_data');
        DbTableHelper::dropStreamTables('story');
    }

    protected function createStorySetsTable(): void
    {
        Schema::create('story_sets', function (Blueprint $table) {
            $table->integerIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->unsignedTinyInteger('auto_archive')->default(1);
            $table->bigInteger('expired_at');
            $table->timestamps();
            $table->unique(['user_id']);
        });
    }

    protected function createStoryTable(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('set_id');
            $table->unsignedTinyInteger('in_process')->default(0);
            $table->unsignedTinyInteger('is_favorite')->default(0);
            $table->unsignedTinyInteger('is_archive')->default(0);
            $table->unsignedInteger('duration')->default(15);
            $table->string('type')->nullable();

            DbTableHelper::viewColumn($table);
            DbTableHelper::setupResourceColumns($table, true, true, true, false);
            DbTableHelper::imageColumns($table);
            DbTableHelper::imageColumns($table, 'thumbnail_file_id');
            DbTableHelper::imageColumns($table, 'video_file_id');
            DbTableHelper::imageColumns($table, 'background_id');
            DbTableHelper::totalColumns($table, ['comment', 'reply', 'like', 'share', 'view', 'play', 'pending_comment', 'pending_reply', 'tag_friend']);
            DbTableHelper::approvedColumn($table);

            $table->json('extra')->nullable()->default(null);
            $table->bigInteger('expired_at');
            $table->timestamps();
        });
    }

    protected function createStoryCollectionsTable(): void
    {
        Schema::create('story_background_set', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('title', 100);

            DbTableHelper::imageColumns($table);

            $table->unsignedBigInteger('main_background_id')->default(0)->index();
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('is_default')->default(0);
            $table->unsignedTinyInteger('view_only')->default(0);
            $table->unsignedTinyInteger('is_deleted')->default(0);
            DbTableHelper::totalColumns($table, ['background']);
            $table->unsignedInteger('ordering')->default(0);
            $table->timestamps();
        });
    }

    protected function createStoryBackgroundsTable(): void
    {
        Schema::create('story_backgrounds', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedBigInteger('set_id');

            DbTableHelper::imageColumns($table);

            $table->string('icon_path')->nullable();
            $table->string('image_path')->nullable();
            $table->string('server_id')->nullable();
            $table->unsignedTinyInteger('view_only')->default(0);
            $table->unsignedTinyInteger('is_deleted')->default(0);
            DbTableHelper::totalColumns($table, ['item']);
            $table->unsignedInteger('ordering')->default(0);
            $table->timestamps();
        });
    }

    protected function createStoryViewsTable(): void
    {
        Schema::create('story_views', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedBigInteger('story_id');
            DbTableHelper::morphUserColumn($table);
            $table->timestamps();
            $table->unique(['user_id', 'story_id']);
        });
    }

    protected function createStoryReactionsTable(): void
    {
        Schema::create('story_reactions', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedBigInteger('story_id');
            DbTableHelper::morphUserColumn($table);
            $table->timestamps();
            $table->unique(['user_id', 'story_id']);
        });
    }

    protected function createStoryReactionsDataTable(): void
    {
        Schema::create('story_reaction_data', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedBigInteger('story_reaction_id');
            DbTableHelper::morphItemColumn($table);
        });
    }
};
