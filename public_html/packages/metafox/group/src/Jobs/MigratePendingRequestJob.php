<?php

namespace MetaFox\Group\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Request as RequestModels;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Jobs\AbstractJob;

class MigratePendingRequestJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $queryGroup = Group::query()
            ->select('groups.id')
            ->whereIn('privacy_type', [PrivacyTypeHandler::CLOSED, PrivacyTypeHandler::SECRET]);

        $query = RequestModels::query()
            ->where('group_requests.status_id', RequestModels::STATUS_PENDING)
            ->whereIn('group_requests.group_id', $queryGroup);

        $query->leftJoin('group_members', function (JoinClause $joinClause) use ($queryGroup) {
            $joinClause->on('group_members.user_id', '=', 'group_requests.user_id')
                ->whereIn('group_members.group_id', $queryGroup);
        })->whereNotNull('group_members.id');

        $query->select('group_requests.*')->delete();
    }
}
