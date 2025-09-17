<?php

namespace MetaFox\Platform\Support\Repository\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User as ContractUser;

/**
 * Interface HasSponsorInFeed.
 */
interface HasSponsorInFeed
{
    /**
     * @param  ContractUser $context
     * @param  int          $id
     * @return bool
     */
    public function unsponsorInFeed(ContractUser $context, int $id): bool;

    /**
     * @throws AuthorizationException
     */
    public function sponsorInFeed(ContractUser $context, int $id, int $newValue): bool;

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isFeedSponsored(Content $model): bool;
}
