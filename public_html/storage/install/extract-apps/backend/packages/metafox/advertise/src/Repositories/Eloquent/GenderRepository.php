<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\GenderRepositoryInterface;
use MetaFox\Advertise\Models\Gender;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class GenderRepository.
 */
class GenderRepository extends AbstractRepository implements GenderRepositoryInterface
{
    public function model()
    {
        return Gender::class;
    }

    public function addGenders(Entity $entity, ?array $genders = null): void
    {
        if (null === $genders) {
            $genders = [];
        }

        $entity->genders()->syncWithPivotValues($genders, ['item_type' => $entity->entityType()]);
    }

    public function deleteGenders(Entity $entity): void
    {
        $entity->genders()->sync([]);
    }
}
