<?php

namespace MetaFox\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteCategoryJob.
 */
class DeleteEventJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $data;

    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    public function handle()
    {
        $eventRepository = resolve(EventRepositoryInterface::class);
        $eventRepository->sendEventDeletedNotifications($this->data);
    }
}
