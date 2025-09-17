<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Support\InviteType;

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
        if (!Schema::hasTable('group_invites')) {
            return;
        }

        $table = Invite::query()->getModel()->getTable();

        $subQuery = Invite::query()
            ->selectRaw("$table.group_id, $table.owner_id, count($table.owner_id) as total_owner")
            ->where(function (Builder $query) use ($table) {
                $query->whereNull("$table.expired_at")
                    ->orWhere("$table.expired_at", '>=', Carbon::now()->toDateTimeString());
            })->whereIn("$table.invite_type", [InviteType::INVITED_MEMBER, InviteType::INVITED_GENERATE_LINK])
            ->groupBy("$table.group_id", "$table.owner_id");

        Invite::query()
            ->joinSub($subQuery, 'sub', 'sub.group_id', '=', "$table.group_id")
            ->where('sub.total_owner', '>', 1)
            ->where("$table.invite_type", InviteType::INVITED_MEMBER)
            ->where("$table.status_id", Invite::STATUS_PENDING)
            ->update([
                "$table.status_id" => Invite::STATUS_NOT_USE,
            ]);

        // to do here

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('group_activities');
    }
};
