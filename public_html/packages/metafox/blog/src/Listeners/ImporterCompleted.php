<?php

namespace MetaFox\Blog\Listeners;

use MetaFox\Blog\Jobs\MigrateCategoryRelation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
    }
}
