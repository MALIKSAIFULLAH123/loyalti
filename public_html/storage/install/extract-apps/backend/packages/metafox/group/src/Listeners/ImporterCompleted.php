<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Jobs\MigrateCategoryRelation;
use MetaFox\Group\Jobs\MigrateGroupCover;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
        MigrateGroupCover::dispatch();
    }
}
