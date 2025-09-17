<?php

use MetaFox\ActivityPoint\Support\ActivityPoint;
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
        if (!Schema::hasTable('apt_transactions')) {
            return;
        }

        \MetaFox\ActivityPoint\Models\PointTransaction::query()
            ->where([
                'type' => ActivityPoint::TYPE_SENT,
                'action' => 'activitypoint::phrase.your_point_has_been_revoked_by_the_administrator'
            ])
            ->update([
                'type' => ActivityPoint::TYPE_RETRIEVED
            ]);
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
