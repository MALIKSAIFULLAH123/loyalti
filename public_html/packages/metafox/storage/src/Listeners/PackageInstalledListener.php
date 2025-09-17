<?php

namespace MetaFox\Storage\Listeners;

class PackageInstalledListener
{
    public function handle(string $package): void
    {
        app('asset')->publishAssets($package);
    }
}
