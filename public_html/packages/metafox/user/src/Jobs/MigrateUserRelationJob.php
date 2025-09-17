<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Models\UserRelationHistory;

class MigrateUserRelationJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $userIds = DB::table('activity_feeds')->where([
            'type_id'   => User::USER_UPDATE_RELATIONSHIP_ENTITY_TYPE,
            'item_type' => UserProfile::ENTITY_TYPE,
        ])->pluck('item_id')->toArray();

        $userProfiles = DB::table('user_profiles')
            ->whereIn('id', $userIds)
            ->get(['id', 'relation', 'relation_with'])
            ->toArray();

        foreach ($userProfiles as $userProfile) {
            $relationHistory = UserRelationHistory::query()->newModelInstance([
                'user_id'       => $userProfile['id'],
                'user_type'     => User::ENTITY_TYPE,
                'relation_id'   => $userProfile['relation'],
                'relation_with' => $userProfile['relation_with'],
            ]);

            $relationHistory->saveQuietly();

            DB::table('activity_feeds')->where([
                'type_id'   => User::USER_UPDATE_RELATIONSHIP_ENTITY_TYPE,
                'item_id'   => $userProfile['id'],
                'item_type' => UserProfile::ENTITY_TYPE,
            ])->update([
                'item_id'   => $relationHistory->entityId(),
                'item_type' => UserRelationHistory::ENTITY_TYPE,
            ]);
        }
    }
}
