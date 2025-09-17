<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Jobs\MigrateNotificationData;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateNotificationData::dispatch();
    }
}
