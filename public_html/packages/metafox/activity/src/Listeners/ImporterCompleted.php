<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Jobs\MigrateFeedContent;
use MetaFox\Activity\Jobs\MigrateFeedOwner;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateFeedContent::dispatch(true);
        MigrateFeedOwner::dispatch(true);
    }
}
