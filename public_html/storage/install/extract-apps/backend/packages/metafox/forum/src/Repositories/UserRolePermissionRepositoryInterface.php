<?php

namespace MetaFox\Forum\Repositories;

use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserRolePermission.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface UserRolePermissionRepositoryInterface
{
    public function getPermissionOptions(): array;

    public function updateRolePermission(User $context, Forum $forum, array $data): bool;

    public function getAllPermissionByForumId(int $forumId): array;

    public function hasAccess(int $userRoleId, int|null $forumId, string $permission): bool;
}
