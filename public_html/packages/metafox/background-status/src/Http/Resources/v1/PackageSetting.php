<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\BackgroundStatus\Http\Resources\v1;

use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(BgsCollectionRepositoryInterface $collectionRepository): array
    {
        return [
            'total_active' => $collectionRepository->getTotalCollectionActive(),
        ];
    }

    public function getMobileSettings(BgsCollectionRepositoryInterface $collectionRepository): array
    {
        return [
            'total_active' => $collectionRepository->getTotalCollectionActive(),
        ];
    }
}
