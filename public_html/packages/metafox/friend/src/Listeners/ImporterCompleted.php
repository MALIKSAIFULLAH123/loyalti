<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Jobs\MigrateFriendStatistic;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateFriendStatistic::dispatch();
    }
}
