<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Advertise\Repositories\LanguageRepositoryInterface;
use MetaFox\Advertise\Models\Language;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class LanguageRepository.
 */
class LanguageRepository extends AbstractRepository implements LanguageRepositoryInterface
{
    public function model()
    {
        return Language::class;
    }

    public function addLanguages(Entity $entity, ?array $languages): void
    {
        if (null === $languages) {
            $languages = [];
        }

        $entity->languages()->syncWithPivotValues($languages, ['item_type' => $entity->entityType()]);
    }

    public function deleteLanguages(Entity $entity): void
    {
        $entity->languages()->sync([]);
    }
}
