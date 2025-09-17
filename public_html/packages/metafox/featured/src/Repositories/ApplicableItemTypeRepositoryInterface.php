<?php

namespace MetaFox\Featured\Repositories;

use MetaFox\Featured\Models\Package;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ApplicableItemType
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ApplicableItemTypeRepositoryInterface
{
    /**
     * @param Package $package
     * @param array   $itemTypes
     * @return bool
     */
    public function updateForPackage(Package $package, array $itemTypes): bool;

    /**
     * @param Package $package
     * @return bool
     */
    public function deleteForPackage(Package $package): bool;
}
