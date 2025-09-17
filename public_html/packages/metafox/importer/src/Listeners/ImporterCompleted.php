<?php

namespace MetaFox\Importer\Listeners;

use MetaFox\Importer\Jobs\MigrateLikeComment;

class ImporterCompleted
{
    public function handle(): void
    {
        MigrateLikeComment::dispatch();
    }
}
