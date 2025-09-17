<?php

namespace MetaFox\Form;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Models\UserRelation;
use MetaFox\User\Repositories\UserRelationRepositoryInterface;

/**
 * Trait RelationTrait.
 */
trait RelationTrait
{
    /**
     * @return array
     */
    public function getRelations(): array
    {
        $repository      = resolve(UserRelationRepositoryInterface::class);
        $phpfoxRelations = $repository->getRelations();
        $data            = [];

        foreach ($phpfoxRelations as $relation) {
            /* @var UserRelation $relation */
            $data[] = [
                'value' => $relation->entityId(),
                'label' => __p($relation->phrase_var),
            ];
        }

        return $data;
    }

    /**
     * @return array<int>
     */
    public function getWithRelations(): array
    {
        return [
            MetaFoxConstant::RELATION_ENGAGED,
            MetaFoxConstant::RELATION_MARRIED,
            MetaFoxConstant::RELATION_IN_A_OPEN_RELATIONSHIP,
            MetaFoxConstant::RELATION_IN_A_RELATIONSHIP,
            MetaFoxConstant::RELATION_IN_A_RELATIONSHIP,
        ];
    }
}
