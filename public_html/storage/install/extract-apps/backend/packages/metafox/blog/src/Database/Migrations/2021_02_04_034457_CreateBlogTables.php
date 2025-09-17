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
        if (!Schema::hasTable('blogs')) {
            Schema::create('blogs', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, true, true, true);

                DbTableHelper::totalColumns($table, ['view', 'like', 'comment', 'reply', 'share', 'attachment']);

                $table->string('title', 255);

                $table->unsignedTinyInteger('is_draft')->default(0);

                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::approvedColumn($table);
                DbTableHelper::imageColumns($table);
                DbTableHelper::tagsColumns($table);

                $table->timestamps();
            });
        }

        DbTableHelper::categoryTable('blog_categories', true);
        DbTableHelper::categoryDataTable('blog_category_data');
        DbTableHelper::createTagDataTable('blog_tag_data');
        DbTableHelper::textTable('blog_text');
        DbTableHelper::streamTables('blog');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DbTableHelper::dropStreamTables('blog');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_text');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('blog_category_data');
        Schema::dropIfExists('blog_tag_data');
    }
};
