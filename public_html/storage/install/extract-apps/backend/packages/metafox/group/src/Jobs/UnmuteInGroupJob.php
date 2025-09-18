<?php

namespace MetaFox\Group\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Group\Models\Mute;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class UnMuteInGroupJob.
 * @ignore
 */
class UnmuteInGroupJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        /** @var MuteRepositoryInterface $muteRepository */
        $muteRepository = resolve(MuteRepositoryInterface::class);
        $member         = $muteRepository->getModel()->newModelQuery()
            ->where('status', Mute::STATUS_MUTED)
            ->where(function (Builder $builder) {
                $builder->whereNotNull('expired_at')
                    ->where('expired_at', '<=', Carbon::now()->toDateTimeString());
            });
        $member->delete();
    }
}
