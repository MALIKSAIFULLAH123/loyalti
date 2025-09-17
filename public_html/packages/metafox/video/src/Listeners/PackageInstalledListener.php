<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Core\Repositories\DriverRepositoryInterface;

class PackageInstalledListener
{
    /**
     * @param string $package
     * @return void
     */
    public function handle(string $package): void
    {
        $this->removeOldMuxDrivers($package);
    }

    protected function removeOldMuxDrivers(string $package): void
    {
        if ($package !== 'metafox/video') {
            return;
        }
        
        resolve(DriverRepositoryInterface::class)
            ->getModel()
            ->newModelQuery()
            ->where('package_id', '=', $package)
            ->where('name', '=', 'video.mux')
            ->delete();
    }
}
