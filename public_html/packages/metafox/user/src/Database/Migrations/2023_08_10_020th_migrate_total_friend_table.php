<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $model        = new \MetaFox\User\Models\User();
        $updateColumn = 'total_friend';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Friend\Models\Friend::query()
            ->selectRaw('owner_id, count(*) as aggregate')
            ->where('owner_type', 'user')
            ->groupBy('owner_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $query,
            'id',
            'owner_id',
            false
        );

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('users', 'total_friends');
    }
};
