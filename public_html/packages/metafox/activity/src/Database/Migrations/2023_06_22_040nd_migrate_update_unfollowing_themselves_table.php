<?php

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use MetaFox\User\Models\User;

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
        $dataInsert = User::query()
            ->select('users.id as user_id', 'users.id as owner_id')
            ->leftJoin('activity_subscriptions', function (JoinClause $join) {
                $join->on('users.id', 'activity_subscriptions.user_id');
                $join->on('activity_subscriptions.user_id', 'activity_subscriptions.owner_id');
            })
            ->whereNull('activity_subscriptions.user_id')
            ->get()
            ->toArray();

        DB::table('activity_subscriptions')->insert($dataInsert);
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
