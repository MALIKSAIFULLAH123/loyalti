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
        if (!Schema::hasTable('polls')) {
            Schema::create('polls', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::viewColumn($table);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->string('question');
                $table->string('caption')->nullable();
                DbTableHelper::imageColumns($table);
                $table->unsignedTinyInteger('randomize')->default(1);
                $table->unsignedTinyInteger('public_vote')->default(0);
                $table->unsignedTinyInteger('is_multiple')->default(0);
                $table->timestamp('closed_at')->nullable();
                DbTableHelper::approvedColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::totalColumns($table, ['like', 'comment', 'reply', 'share', 'view', 'attachment', 'vote']);
                DbTableHelper::locationColumn($table);
                $table->timestamps();
            });
        }

        // Create Poll Text table
        DbTableHelper::textTable('poll_text');

        if (!Schema::hasTable('poll_answers')) {
            Schema::create('poll_answers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('poll_id');
                $table->string('answer');
                $table->decimal('percentage', 5, 2, true)->default(0.00);
                DbTableHelper::totalColumns($table, ['vote']);
                $table->unsignedTinyInteger('ordering')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('poll_designs')) {
            Schema::create('poll_designs', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->string('background')->nullable(false)->default('ebebeb');
                $table->string('percentage')->nullable(false)->default('297fc7');
                $table->string('border')->nullable(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('poll_results')) {
            Schema::create('poll_results', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('poll_id');
                $table->unsignedBigInteger('answer_id');
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();
            });
        }

        DbTableHelper::streamTables('poll');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('polls');
        Schema::dropIfExists('poll_text');
        Schema::dropIfExists('poll_answers');
        Schema::dropIfExists('poll_results');
        Schema::dropIfExists('poll_designs');
        DbTableHelper::dropStreamTables('poll');
    }
};
