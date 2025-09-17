<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Jobs\MigrateCategoryRelation;
use MetaFox\Page\Jobs\MigratePageAvatar;
use MetaFox\Page\Jobs\MigratePageCover;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
        MigratePageAvatar::dispatch();
        MigratePageCover::dispatch();
    }
}
