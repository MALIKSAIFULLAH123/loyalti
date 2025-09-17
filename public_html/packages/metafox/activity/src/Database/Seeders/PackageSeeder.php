<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Database\Seeders;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Activity\Support\Facades\ActivitySubscription;
use MetaFox\Activity\Support\Support;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class PackageSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageSeeder extends Seeder
{
    public function run()
    {
        $this->addMissingActivitySubscription();
    }

    protected function addMissingActivitySubscription(): void
    {
        if (!Schema::hasTable('activity_subscriptions')) {
            return;
        }

        $superAdmin = resolve(UserRepositoryInterface::class)->getSuperAdmin();

        if (null === $superAdmin) {
            return;
        }

        $userIds = DB::table('users')
            ->select(['users.id'])
            ->leftJoin('activity_subscriptions', function (JoinClause $joinClause) use ($superAdmin) {
                $joinClause->on('activity_subscriptions.user_id', '=', 'users.id')
                    ->where('activity_subscriptions.owner_id', '=', $superAdmin->entityId())
                    ->where('activity_subscriptions.special_type', '=', Support::ACTIVITY_SUBSCRIPTION_VIEW_SUPER_ADMIN_FEED);
            })
            ->whereNull('activity_subscriptions.id')
            ->where('users.id', '<>', $superAdmin->entityId())
            ->get()
            ->pluck('id')
            ->toArray();

        if (!count($userIds)) {
            return;
        }

        foreach ($userIds as $userId) {
            ActivitySubscription::addSubscription($userId, $superAdmin->entityId(), true, Support::ACTIVITY_SUBSCRIPTION_VIEW_SUPER_ADMIN_FEED);
        }
    }
}
