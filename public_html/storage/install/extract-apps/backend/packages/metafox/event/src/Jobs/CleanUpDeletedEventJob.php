<?php

namespace MetaFox\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Support\Facades\UserEntity;

class CleanUpDeletedEventJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $period = 0; //@todo: Should this value be converted to a setting?!
        $this->cleanUpTrash($period);
    }

    protected function cleanUpTrash(int $period = 1): void
    {
        $timestamp = Carbon::now()->subDays($period);

        Event::onlyTrashed()
            ->where('deleted_at', '<=', $timestamp)
            ->get()
            ->collect()
            ->each(function (Event $event) {
                UserEntity::forceDeleteEntity($event->entityId());
                $event->forceDelete();
            });
    }
}
