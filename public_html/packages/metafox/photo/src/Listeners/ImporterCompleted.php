<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Jobs\MigrateCategoryRelation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
    }
}
