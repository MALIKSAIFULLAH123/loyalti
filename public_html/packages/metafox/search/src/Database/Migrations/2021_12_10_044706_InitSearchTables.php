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
        $this->createSearchItemTables();
        $this->createPrivacyStreamTable();
        $this->createTypes();
        $this->createSearchTagData();
    }

    protected function createSearchTagData()
    {
        if (!Schema::hasTable('search_tag_data')) {
            Schema::create('search_tag_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedInteger('tag_id')->index();
                $table->unique(['item_id', 'tag_id'], 'item_id_tag_id');
            });
        }
    }

    protected function createPrivacyStreamTable()
    {
        DbTableHelper::streamTables('search');
    }

    protected function createSearchItemTables()
    {
        if (!Schema::hasTable('search_items')) {
            Schema::create('search_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::privacyColumn($table);
                $table->mediumText('title');
                $table->mediumText('text');
                $table->timestamps();

                $table->unique(['item_id', 'item_type', 'privacy'], 'item_privacy');
            });
        }

        DbTableHelper::createFullTextIndex('search_items', 'search_text', ['title', 'text']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DbTableHelper::dropFullTextIndex('search_items', 'search_text');
        Schema::dropIfExists('search_items');
        Schema::dropIfExists('search_types');
        Schema::dropIfExists('search_tag_data');
        DbTableHelper::dropStreamTables('search');
    }

    protected function createTypes(): void
    {
        if (!Schema::hasTable('search_types')) {
            Schema::create('search_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type')->unique();
                DbTableHelper::moduleColumn($table);
                DbTableHelper::entityTypeColumn($table);
                $table->string('title');
                $table->mediumText('description')->nullable(true);
                $table->unsignedInteger('is_active')->default(1);
                $table->unsignedInteger('system_value')->default(0);
                $table->unsignedInteger('is_system')->default(1);

                $table->text('params')->nullable();
            });
        }
    }
};
