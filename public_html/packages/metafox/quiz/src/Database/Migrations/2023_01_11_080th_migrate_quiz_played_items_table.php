<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;

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
        if (!Schema::hasTable('quiz_played_results')) {
            Schema::create('quiz_played_results', function (Blueprint $table) {
                $table->bigInteger('quiz_id');
                $table->bigInteger('user_id');
                $table->unique(['quiz_id', 'user_id']);
            });
        }

        $results = Result::query()->getModel()->get();
        foreach ($results as $result) {
            $playResult = resolve(ResultRepositoryInterface::class)->getPlayResult($result->quiz_id, $result->user_id);
            if (empty($playResult)) {
                resolve(ResultRepositoryInterface::class)->createPlayResult($result->quiz_id, $result->user_id);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_played_items');
    }
};
