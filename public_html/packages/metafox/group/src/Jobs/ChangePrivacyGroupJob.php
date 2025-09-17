<?php

namespace MetaFox\Group\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupChangePrivacy;
use MetaFox\Group\Repositories\GroupChangePrivacyRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class ChangePrivacyGroupJob extends AbstractJob implements ShouldBeUnique
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
        /** @var GroupChangePrivacyRepositoryInterface $changePrivacyRepository */
        $changePrivacyRepository = resolve(GroupChangePrivacyRepositoryInterface::class);

        $items = $this->getExpiredQueryBuilder()->get();

        if ($items->isEmpty()) {
            return;
        }

        $items->each(function (GroupChangePrivacy $item) use ($changePrivacyRepository) {
            $group = $item->group;

            if (!$group instanceof Group) {
                return;
            }

            $changePrivacyRepository->sentNotificationWhenSuccess($item->entityId());
            $changePrivacyRepository->updatePrivacyGroup($item->user, $group, $item->privacy_type);
        });

        $this->getExpiredQueryBuilder()->update([
            'is_active' => GroupChangePrivacy::IS_NOT_ACTIVE,
        ]);
    }

    protected function getExpiredQueryBuilder(): Builder
    {
        $now   = Carbon::now();
        $model = new GroupChangePrivacy();

        return $model->newQuery()
            ->where('is_active', GroupChangePrivacy::IS_ACTIVE)
            ->whereDate('expired_at', '<=', $now);
    }
}
