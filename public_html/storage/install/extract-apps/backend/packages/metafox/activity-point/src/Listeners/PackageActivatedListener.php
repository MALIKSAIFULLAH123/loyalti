<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Jobs\MigratePointTransaction;

/**
 * Class PackageActivatedListener.
 * @ignore
 */
class PackageActivatedListener
{
    /**
     * @param string $package
     *
     * @throws \Throwable
     */
    public function handle(string $package): void
    {
        $this->handleActionType($package);
    }

    private function handleActionType(string $packageId): void
    {
        MigratePointTransaction::dispatch($packageId);
    }
}
