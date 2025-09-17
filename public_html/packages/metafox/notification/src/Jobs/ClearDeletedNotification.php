<?php

namespace MetaFox\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class ClearDeletedNotification extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Clean up deleted notifications in trash.
     * This event does trigger the forceDelete event observer.
     *
     * @param NotificationRepositoryInterface $repository
     *
     * @return void
     */
    public function handle(NotificationRepositoryInterface $repository): void
    {
        $repository->cleanUpTrash();
    }
}
