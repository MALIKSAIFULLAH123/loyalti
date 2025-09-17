<?php

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Jobs\MigrateAlbumGenre;
use MetaFox\Music\Jobs\MigrateCategoryRelation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateAlbumGenre::dispatch();
        MigrateCategoryRelation::dispatch();
    }
}
