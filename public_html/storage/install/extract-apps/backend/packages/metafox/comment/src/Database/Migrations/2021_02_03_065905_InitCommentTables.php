<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Comment\Support\Helper;
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
        $this->initComment();
        $this->initCommentHidden();
        $this->initCommentAttachments();
        $this->initCommentTagData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comment_attachments');
        Schema::dropIfExists('comment_hidden');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comment_tag_data');
    }

    /**
     * @return void
     */
    protected function initComment()
    {
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('parent_id')->default(0)->index();

                DbTableHelper::morphItemColumn($table);

                DbTableHelper::morphUserColumn($table);

                DbTableHelper::morphOwnerColumn($table);

                DbTableHelper::approvedColumn($table);

                $table->unsignedTinyInteger('is_spam')
                    ->default(0)
                    ->index();

                DbTableHelper::moduleColumn($table);

                DbTableHelper::totalColumns($table, ['comment', 'like']);

                $table->mediumText('text');

                $table->mediumText('text_parsed');

                $table->timestamps();
            });
        }
    }

    /**
     * @return void
     */
    protected function initCommentHidden()
    {
        if (!Schema::hasTable('comment_hidden')) {
            Schema::create('comment_hidden', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                $table->unsignedBigInteger('item_id')
                    ->index();

                $table->enum('type', Helper::getHideTypes())
                    ->default(Helper::HIDE_OWN)
                    ->index();

                $table->boolean('is_hidden')
                    ->default(true)
                    ->index();

                $table->index(['item_id', 'user_id'], 'comment_item_user');
            });
        }
    }

    /**
     * @return void
     */
    protected function initCommentAttachments(): void
    {
        if (!Schema::hasTable('comment_attachments')) {
            Schema::create('comment_attachments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('comment_id')->index();
                DbTableHelper::morphItemColumn($table, true);
                $table->mediumText('params')->nullable();
                $table->softDeletes();
            });
        }
    }

    protected function initCommentTagData()
    {
        DbTableHelper::createTagDataTable('comment_tag_data');
    }
};
