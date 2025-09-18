<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Jobs\MigrateCategoryRelation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
    }
}
