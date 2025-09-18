<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Block
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait
 */
interface BlockRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewGroupBlocks(User $context, array $attributes): Paginator;

    /**
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function isBlocked(int $groupId, int $userId): bool;

    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     * @return bool
     */
    public function addGroupBlock(User $context, int $groupId, array $attributes): bool;

    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     * @return bool
     */
    public function deleteGroupBlock(User $context, int $groupId, array $attributes): bool;
}
