<?php

namespace MetaFox\Saved\Support;

use MetaFox\Platform\PackageManager;
use MetaFox\Saved\Contracts\Support\SavedTypeContract;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * Should be moved into database settings.
 */
class SavedType implements SavedTypeContract
{
    public function getFilterOptions(): array
    {
        return resolve(SavedRepositoryInterface::class)->getFilterOptions();
    }

    /**
     * @inheritDoc
     */
    public function transformItemType(): array
    {
        $moduleSavedItemTypes = PackageManager::discoverSettings('getSavedTypes');
        $dataValues           = [];
        foreach ($moduleSavedItemTypes as $module => $values) {
            foreach ($values as $value) {
                $dataValues[$value['value']] = $value['label'];
            }
        }

        return $dataValues;
    }
}
