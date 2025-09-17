<?php

namespace MetaFox\Advertise\Repositories;

use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\AdvertiseHide;
use MetaFox\Platform\Contracts\Entity;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface AdvertiseHide.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface AdvertiseHideRepositoryInterface
{
    /**
     * @param  User          $context
     * @param  Entity        $item
     * @return AdvertiseHide
     */
    public function createHide(User $context, Entity $item): AdvertiseHide;

    /**
     * @param  User   $context
     * @param  Entity $item
     * @return bool
     */
    public function deleteHide(User $context, Entity $item): bool;

    /**
     * @param  Entity $item
     * @return void
     */
    public function deleteHidesByItem(Entity $item): void;

    /**
     * @param  User   $context
     * @param  Entity $item
     * @return bool
     */
    public function isHidden(User $context, Entity $item): bool;

    /**
     * @param  User   $context
     * @param  string $itemType
     * @return array
     */
    public function getHiddenItemIds(User $context, string $itemType): array;
}
