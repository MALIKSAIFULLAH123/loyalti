<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Jobs\MigrateCategoryRelation;
use MetaFox\Event\Jobs\MigrateEventLocation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateEventLocation::dispatch(true);
        MigrateCategoryRelation::dispatch();
    }
}
