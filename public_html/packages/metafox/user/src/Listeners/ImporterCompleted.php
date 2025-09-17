<?php

namespace MetaFox\User\Listeners;

use MetaFox\User\Jobs\MigrateUserAvatar;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateUserAvatar::dispatch();
    }
}
