<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Jobs\MigrateTotalPoint;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateTotalPoint::dispatch();
    }
}
