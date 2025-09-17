<?php

namespace MetaFox\Search\Listeners;

use MetaFox\Platform\PackageManager;
use MetaFox\Search\Models\Search;
use MetaFox\Search\Models\Type;

/**
 * Class PackageDeletingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageDeletingListener
{
    public function handle(string $moduleName): void
    {
        // If this turn is deleted search, return. No need to go further.
        if ($moduleName === 'metafox/search') {
            return;
        }

        $this->cleanUpDatabase($moduleName);
    }

    /**
     * @param string $moduleName
     *
     * @return string[]
     */
    protected function getResourceNames(string $moduleName): array
    {
        return PackageManager::getResourceNames($moduleName);
    }

    public function cleanUpDatabase(string $moduleName): void
    {
        $resourceNames = $this->getResourceNames($moduleName);

        if (empty($resourceNames) || !is_array($resourceNames)) {
            return;
        }

        Type::query()->whereIn('entity_type', $resourceNames)->delete();
        Search::query()->whereIn('item_type', $resourceNames)->delete();
    }
}
