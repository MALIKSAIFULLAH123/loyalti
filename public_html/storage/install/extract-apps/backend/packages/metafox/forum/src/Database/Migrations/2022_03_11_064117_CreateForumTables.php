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
        if (!Schema::hasTable('forums')) {
            Schema::create('forums', function (Blueprint $blueprint) {
                $blueprint->id();
                $this->addTitleColumns($blueprint);
                $blueprint->bigInteger('parent_id', false, true)
                    ->default(0)
                    ->index();
                $this->addIsClosedColumn($blueprint);
                $blueprint->bigInteger('ordering')
                    ->default(0);
                DbTableHelper::totalColumns($blueprint, ['thread']);
                $blueprint->timestamps();
            });
        }

        if (!Schema::hasTable('forum_threads')) {
            Schema::create('forum_threads', function (Blueprint $blueprint) {
                $blueprint->id();
                $this->addTitleColumns($blueprint);
                $blueprint->bigInteger('forum_id', false, true);
                DbTableHelper::setupResourceColumns($blueprint, true, true, false, false);
                $blueprint->string('item_type', 30)
                    ->nullable(true);
                $blueprint->bigInteger('item_id', false, true)
                    ->default(0);
                $blueprint->boolean('is_wiki')
                    ->default(false)
                    ->index();
                $blueprint->boolean('is_sticked')
                    ->default(false)
                    ->index();
                DbTableHelper::totalColumns($blueprint, ['comment', 'view', 'like', 'share', 'attachment']);
                $this->addIsClosedColumn($blueprint);
                DbTableHelper::tagsColumns($blueprint);
                DbTableHelper::approvedColumn($blueprint);
                DbTableHelper::sponsorColumn($blueprint);
                $blueprint->timestamps();
            });
        }

        if (!Schema::hasTable('forum_thread_text')) {
            DbTableHelper::textTable('forum_thread_text');
        }

        if (!Schema::hasTable('forum_thread_last_read')) {
            Schema::create('forum_thread_last_read', function (Blueprint $blueprint) {
                $blueprint->id();
                DbTableHelper::morphUserColumn($blueprint);
                $blueprint->bigInteger('thread_id', false, true)
                    ->index();
                $blueprint->bigInteger('post_id', false, true)
                    ->index();
                $blueprint->timestamps();
            });
        }

        if (!Schema::hasTable('forum_posts')) {
            Schema::create('forum_posts', function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->bigInteger('thread_id', false, true)
                    ->index();
                DbTableHelper::morphUserColumn($blueprint);
                DbTableHelper::morphOwnerColumn($blueprint);
                DbTableHelper::approvedColumn($blueprint);
                DbTableHelper::totalColumns($blueprint, ['attachment', 'like', 'share']);
                $blueprint->timestamps();
            });
        }

        if (!Schema::hasTable('forum_post_text')) {
            DbTableHelper::textTable('forum_post_text');
        }

        if (!Schema::hasTable('forum_post_quotes')) {
            Schema::create('forum_post_quotes', function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->bigInteger('post_id', false, true)
                    ->index();
                $blueprint->bigInteger('quote_id', false, true)
                    ->index();
                DbTableHelper::morphColumn($blueprint, 'quote_user');
                $blueprint->timestamps();
            });
        }

        if (!Schema::hasTable('forum_thread_subscribes')) {
            Schema::create('forum_thread_subscribes', function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->bigInteger('item_id', false, true)
                    ->index();
                DbTableHelper::morphUserColumn($blueprint);
                $blueprint->timestamps();
            });
        }

        DbTableHelper::createTagDataTable('forum_thread_tag_data');

        DbTableHelper::createTagDataTable('forum_post_tag_data');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forums');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('forum_thread_text');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_post_text');
        Schema::dropIfExists('forum_thread_subscribes');
        Schema::dropIfExists('forum_thread_tag_data');
        Schema::dropIfExists('forum_post_tag_data');
        Schema::dropIfExists('forum_thread_last_read');
    }

    /**
     * @param  Blueprint $table
     * @return void
     */
    protected function addIsClosedColumn(Blueprint $table): void
    {
        $table->boolean('is_closed')
            ->default(false)
            ->index();
    }

    /**
     * @param  string $table
     * @param  int    $titleLength
     * @param  bool   $addVanityUrl
     * @return void
     */
    protected function addTitleColumns(Blueprint $table, int $titleLength = 255): void
    {
        $table->string('title', $titleLength);
    }
};
