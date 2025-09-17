<?php

namespace Foxexpert\Sevent\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\SeventFavourite;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface SeventFavourite
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SeventFavouriteRepositoryInterface
{
    public function updateFavourite(ContractUser $context, int $id): bool;
    public function favouriteExists(ContractUser $context, int $id): bool;
}
