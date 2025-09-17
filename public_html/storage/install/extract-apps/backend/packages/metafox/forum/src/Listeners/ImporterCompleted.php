<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Jobs\MigrateActivityLike;
use MetaFox\Forum\Jobs\MigratePostId;
use MetaFox\Forum\Jobs\MigrateStatistic;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateStatistic::dispatch(true);
        MigratePostId::dispatch();
        MigrateActivityLike::dispatch();
    }
}
