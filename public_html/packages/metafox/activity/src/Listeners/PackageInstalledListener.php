<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Support\TypeManager;
use MetaFox\Platform\PackageManager;

/**
 * Class PackageInstalledListener.
 * @ignore
 */
class PackageInstalledListener
{
    /**
     * @param string $package
     *
     * @throws \Throwable
     */
    public function handle(string $package): void
    {
        $listener = PackageManager::getListener($package);

        if (!$listener) {
            return;
        }

        $types = $listener->getActivityTypes();

        /** @var TypeManager::class $activityTypeManager */
        $activityTypeManager = resolve(TypeManager::class);

        if (empty($types)) {
            return;
        }

        foreach ($types as $data) {
            if (empty($data)) {
                continue;
            }
            $data['module_id'] =  PackageManager::getAlias($package);
            $data['package_id'] =  $package;

            $activityTypeManager->makeType($data);
        }

        $activityTypeManager->refresh();
    }
}
