<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\User\Jobs\MigrateUserRelationJob;

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
        if (!Schema::hasTable('user_relation_histories')) {
            Schema::create('user_relation_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->string('user_type');
                $table->unsignedInteger('relation_id');
                $table->unsignedBigInteger('relation_with')->default(0);
            });
        }

        if (Schema::hasTable('user_profiles') && Schema::hasTable('activity_feeds')) {
            MigrateUserRelationJob::dispatchSync();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_relation_histories');
    }
};
