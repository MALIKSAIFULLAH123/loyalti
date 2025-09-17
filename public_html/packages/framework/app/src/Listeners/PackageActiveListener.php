<?php

namespace MetaFox\App\Listeners;

use MetaFox\App\Repositories\PackageRepositoryInterface;

class PackageActiveListener
{
    public function handle(string $appName): bool
    {
        return resolve(PackageRepositoryInterface::class)->isAppActive($appName);
    }
}
