<?php

namespace MetaFox\Page\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Block.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface BlockRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewPageBlocks(User $context, array $attributes): Paginator;

    /**
     * @param User  $context
     * @param int   $pageId
     * @param array $attributes
     * @return bool
     */
    public function addPageBlock(User $context, int $pageId, array $attributes): bool;

    /**
     * @param User  $context
     * @param int   $pageId
     * @param array $attributes
     * @return bool
     */
    public function deletePageBlock(User $context, int $pageId, array $attributes): bool;

    /**
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function isBlocked(int $pageId, int $userId): bool;
}
