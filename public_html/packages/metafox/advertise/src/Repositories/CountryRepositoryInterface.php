<?php

namespace MetaFox\Advertise\Repositories;

use MetaFox\Platform\Contracts\Entity;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Country.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface CountryRepositoryInterface
{
    /**
     * @param  Entity     $entity
     * @param  array|null $locations
     * @return void
     */
    public function createLocation(Entity $entity, ?array $locations): void;

    /**
     * @param  Entity $entity
     * @return void
     */
    public function deleteLocations(Entity $entity): void;
}
