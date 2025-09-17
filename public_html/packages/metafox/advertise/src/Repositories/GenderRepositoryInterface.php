<?php

namespace MetaFox\Advertise\Repositories;

use MetaFox\Platform\Contracts\Entity;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Gender.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface GenderRepositoryInterface
{
    /**
     * @param  Entity     $Entity
     * @param  array|null $genders
     * @return void
     */
    public function addGenders(Entity $entity, ?array $genders = null): void;

    /**
     * @param  Entity $Entity
     * @return void
     */
    public function deleteGenders(Entity $entity): void;
}
