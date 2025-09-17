<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\ResultDetail;

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
        if (!Schema::hasColumn('quiz_answers', 'total_play')) {
            Schema::table('quiz_answers', function (Blueprint $table) {
                $table->integer('total_play')
                    ->default(0);
            });
        }

        $answerResults = ResultDetail::query()->getModel()
            ->select('answer_id', DB::raw('COUNT(*) as total_play'))
            ->groupBy('answer_id')
            ->get()
            ->toArray();

        foreach ($answerResults as $answerResult) {
            Answer::query()->getModel()
                ->where('id', '=', $answerResult['answer_id'])
                ->update([
                    'total_play' => $answerResult['total_play'] ?? 0,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('quiz_answers', 'total_play')) {
            Schema::table('total_play', function (Blueprint $table) {
                $table->dropColumn(['total_play']);
            });
        }
    }
};
