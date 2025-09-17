<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (!Schema::hasTable('apt_statistics') || !Schema::hasTable('user_values')) {
            return;
        }

        $userValues = \MetaFox\User\Models\UserValue::query()
            ->where('name', 'total_activity_points')
            ->get(['value', 'user_id']);

        if (!$userValues->count()) {
            return;
        }

        $upserts = $userValues->map(function (\MetaFox\User\Models\UserValue $userValue) {
            return [
                'id' => $userValue->user_id,
                'current_points' => $userValue->value,
            ];
        })->toArray();

        \MetaFox\ActivityPoint\Models\PointStatistic::upsert($upserts, ['id'], ['current_points']);
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
