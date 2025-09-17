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
        if (!Schema::hasTable('core_item_statistics')) {
            Schema::create('core_item_statistics', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphItemColumn($table);

                DbTableHelper::totalColumns($table, ['pending_comment', 'pending_reply']);
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
        Schema::dropIfExists('core_item_statistics');
    }
};
