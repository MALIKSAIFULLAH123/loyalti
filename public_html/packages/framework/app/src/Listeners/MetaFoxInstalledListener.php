<?php

namespace MetaFox\App\Listeners;

use MetaFox\App\Support\MetaFoxStore;

class MetaFoxInstalledListener
{
    public function handle(): void
    {
        app(MetaFoxStore::class)->increaseInstallAppStatistic();
    }
}
