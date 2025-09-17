<?php

namespace Foxexpert\Sevent\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use Foxexpert\Sevent\Repositories\SeventFavouriteRepositoryInterface;
use Foxexpert\Sevent\Models\SeventFavourite;
use MetaFox\Platform\Contracts\User as ContractUser; 
use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\ViewScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class SeventFavouriteRepository
 *
 */
class SeventFavouriteRepository extends AbstractRepository implements SeventFavouriteRepositoryInterface
{
    public function model()
    {
        return SeventFavourite::class;
    }

    public function favouriteExists(ContractUser $context, int $id): bool 
    {
        $query = $this->getModel()
            ->newQuery()
            ->where('sevent_favourite.sevent_id', '=', $id)
            ->where('sevent_favourite.owner_id', '=', $context->entityId())
            ->where('sevent_favourite.owner_type', '=', $context->entityType());
        
        $aRow = $query->first();
        if ($aRow)
            return true;

        return false;
    }
    
    public function updateFavourite(ContractUser $context, int $id): bool
    {
        $query = $this->getModel()
            ->newQuery()
            ->where('sevent_favourite.sevent_id', '=', $id)
            ->where('sevent_favourite.owner_id', '=', $context->entityId())
            ->where('sevent_favourite.owner_type', '=', $context->entityType());

        $play = $query->first();
        if (!$play)
            $this->createFavourite($context, $id);
        else
            $play->delete();
        
        return true;
    }

    public function createFavourite(ContractUser $context, int $id): SeventFavourite
    {
        $attributes = [
            'owner_id'     => $context->entityId(),
            'owner_type'   => $context->entityType(),
            'sevent_id'   => $id
        ];

        $seventPlay = new SeventFavourite($attributes);
        $seventPlay->save();

        return $seventPlay;
    }
}
