<?php

namespace MetaFox\Friend\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingFriendStatistic extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $userIds)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $update = [
            'total_friend' => DB::raw('(SELECT COUNT(*) FROM friends JOIN users AS u ON u.id = friends.user_id WHERE friends.owner_type = \'user\' AND friends.owner_id = users.id)'),
        ];
        if (DB::table('activity_subscriptions')->exists()) {
            $update['total_follower']  = DB::raw('(SELECT COUNT(*) FROM activity_subscriptions AS acs JOIN users AS u ON u.id = acs.user_id WHERE acs.owner_id = users.id AND acs.user_id != users.id AND acs.is_active = true AND acs.special_type IS NULL)');
            $update['total_following'] = DB::raw('(SELECT COUNT(*) FROM activity_subscriptions AS acs JOIN users AS u ON u.id = acs.owner_id WHERE acs.user_id = users.id AND acs.owner_id != users.id AND acs.is_active = true AND acs.special_type IS NULL)');
        }
        DB::table('users')->whereIn('users.id', $this->userIds)->update($update);
    }
}
