<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        Schema::create('test_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('privacy')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');

            DbTableHelper::totalColumns($table, ['like', 'share', 'comment', 'reply', 'view', 'attachment']);
            $table->timestamps();
        });

        DbTableHelper::streamTables('test');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_items');
    }
};
