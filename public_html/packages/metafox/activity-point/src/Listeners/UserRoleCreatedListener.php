<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Jobs\ClonePointSettingJob;
use MetaFox\Platform\Contracts\Entity;

class UserRoleCreatedListener
{
    public function handle(Entity $role): void
    {
        ClonePointSettingJob::dispatch($role);
    }
}
