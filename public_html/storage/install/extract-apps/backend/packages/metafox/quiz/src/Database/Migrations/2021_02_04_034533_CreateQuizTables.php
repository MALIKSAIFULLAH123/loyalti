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
        if (!Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::viewColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::privacyColumn($table);
                $table->string('title');
                DbTableHelper::imageColumns($table);
                DbTableHelper::totalColumns($table, ['comment', 'reply', 'like', 'share', 'view', 'attachment', 'play']);
                DbTableHelper::approvedColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                $table->timestamps();
            });
        }

        DbTableHelper::textTable('quiz_text');

        if (!Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('quiz_id')->index();
                $table->unsignedSmallInteger('ordering')->default(0);
                $table->string('question');
            });
        }

        if (!Schema::hasTable('quiz_answers')) {
            Schema::create('quiz_answers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('question_id')->index();
                $table->unsignedSmallInteger('ordering')->default(0);
                $table->string('answer');
                $table->unsignedTinyInteger('is_correct')->default(0);
            });
        }

        if (!Schema::hasTable('quiz_results')) {
            Schema::create('quiz_results', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('quiz_id');
                DbTableHelper::totalColumns($table, ['correct']);
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('quiz_result_items')) {
            Schema::create('quiz_result_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('result_id');
                $table->unsignedBigInteger('question_id');
                $table->unsignedBigInteger('answer_id');
                $table->unsignedTinyInteger('is_correct')->default(0);
            });
        }

        DbTableHelper::streamTables('quiz');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_results');
        Schema::dropIfExists('quiz_text');
        DbTableHelper::dropStreamTables('quiz');
    }
};
