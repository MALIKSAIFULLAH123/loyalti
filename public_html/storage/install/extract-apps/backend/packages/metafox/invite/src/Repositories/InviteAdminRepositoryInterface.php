<?php

namespace MetaFox\Invite\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Invite Admin.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait;
 */
interface InviteAdminRepositoryInterface
{
    /**
     * @param User  $user
     * @param array $params
     * @return Paginator
     */
    public function viewInvites(User $user, array $params): Paginator;

    /**
     * @param User $user
     * @param int  $id
     * @return bool
     */
    public function deleteInvite(User $user, int $id): bool;
}
