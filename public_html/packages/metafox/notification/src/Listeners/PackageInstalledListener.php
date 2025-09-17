<?php

namespace MetaFox\Notification\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class PackageInstalledListener
{
    public function handle(string $package): void
    {
        $listener = PackageManager::getListener($package);

        if (!$listener) {
            return;
        }
        $this->handleMakeType($listener);
    }

    protected function handleMakeType(BasePackageSettingListener $listener): void
    {
        $types = $listener->getNotificationTypes();

        /** @var TypeManager $typeManager */
        $typeManager = resolve(TypeManager::class);

        if (empty($types)) {
            return;
        }

        foreach ($types as $data) {
            if (empty($data)) {
                continue;
            }

            if (Arr::get($data, 'is_deleted', false)) {
                $typeManager->handleDeletedTypeByName([Arr::get($data, 'type')]);
                continue;
            }

            $data = Arr::except($data, ['is_deleted']);
            $typeManager->makeType($data);
        }

        $typeManager->refresh();
    }
}
