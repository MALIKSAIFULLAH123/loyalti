<?php

use Illuminate\Database\Migrations\Migration;
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
        $updateColumn = 'total_pending_host_invite';
        if (Schema::hasColumn('events', $updateColumn)) {
            return;
        }

        $model = new MetaFox\Event\Models\Event();

        DbTableHelper::addMisingTotalColumn($model, $updateColumn);

        $query = \MetaFox\Event\Models\HostInvite::query()
            ->selectRaw('event_id, count(*) as aggregate')
            ->where([
                'status_id' => \MetaFox\Event\Models\HostInvite::STATUS_PENDING,
            ])->groupBy('event_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $query,
            'id',
            'event_id',
            false,
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
    }
};
