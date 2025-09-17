<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Quiz\Models\Result;
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
        if (!Schema::hasTable('quiz_results')) {
            return;
        }

        $query = Result::query()->whereDoesntHave('user');

        foreach ($query->cursor() as $result) {
            if (!$result instanceof Result) {
                continue;
            }

            $result->delete();
        }

        if (!Schema::hasTable('quiz_result_items')) {
            return;
        }

        $query = ResultDetail::query()->whereDoesntHave('result');

        foreach ($query->cursor() as $resultItem) {
            if (!$resultItem instanceof ResultDetail) {
                continue;
            }

            $resultItem->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
