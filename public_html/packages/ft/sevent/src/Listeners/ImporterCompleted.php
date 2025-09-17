<?php

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Jobs\MigrateCategoryRelation;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateCategoryRelation::dispatch();
    }
}
