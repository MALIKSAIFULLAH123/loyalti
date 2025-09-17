<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Jobs\MigrateCategoryRelation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
    }
}
