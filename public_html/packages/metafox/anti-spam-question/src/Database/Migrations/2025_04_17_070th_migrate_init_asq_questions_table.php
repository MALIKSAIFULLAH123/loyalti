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
        $this->initQuestionsTable();
        $this->initAnswersTable();
        // to do here
    }

    protected function initQuestionsTable(): void
    {
        if (Schema::hasTable('asq_questions')) {
            return;
        }

        Schema::create('asq_questions', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned();
            $table->string('question');
            DbTableHelper::imageColumns($table);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_case_sensitive')->default(false);
            $table->integer('ordering')->default(0);
            $table->timestamps();
        });

    }

    protected function initAnswersTable(): void
    {
        if (Schema::hasTable('asq_answers')) {
            return;
        }
        Schema::create('asq_answers', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned();
            $table->unsignedInteger('question_id');
            $table->string('answer');
            $table->integer('ordering')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('asq_questions');
        Schema::dropIfExists('asq_answers');
    }
};
