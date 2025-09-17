<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Jobs\UpdateUserJob;
use MetaFox\Platform\Contracts\User;

class UpdateUserItemListener
{
    /**
     *
     * @param User $owner
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(User $owner): void
    {
        UpdateUserJob::dispatch($owner->entityId());
    }
}
