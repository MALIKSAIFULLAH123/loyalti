<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Contracts\ProviderManagerInterface;

class CheckReadyService
{
    public function handle()
    {
        return resolve(ProviderManagerInterface::class)->checkReadyService();
    }
}
