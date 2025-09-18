<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Marketplace\Repositories\InviteLinkRepositoryInterface;
use MetaFox\Marketplace\Models\InviteLink;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InviteLinkRepository.
 */
class InviteLinkRepository extends AbstractRepository implements InviteLinkRepositoryInterface
{
    public function model()
    {
        return InviteLink::class;
    }
}
