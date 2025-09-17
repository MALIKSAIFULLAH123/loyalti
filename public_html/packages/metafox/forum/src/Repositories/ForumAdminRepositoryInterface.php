<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Interface ForumAdminRepositoryInterface.
 * @mixin AbstractRepository
 */
interface ForumAdminRepositoryInterface
{
    /**
     * @param  User       $context
     * @param  array      $attributes
     * @return Collection
     */
    public function viewForumsInAdminCP(User $context, array $attributes = []): Collection;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Forum
     */
    public function createForum(User $context, array $attributes): Forum;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Forum
     */
    public function updateForum(User $context, int $id, array $attributes): Forum;

    /**
     * @param  User     $context
     * @param  int      $id
     * @param  string   $deleteOption
     * @param  int|null $alternativeId
     * @return bool
     */
    public function deleteForum(User $context, int $id, string $deleteOption, ?int $alternativeId = null): bool;

    /**
     * @param  Forum $forum
     * @return array
     */
    public function getForumsForDeleteOption(Forum $forum): array;

    /**
     * @param  Forum $forum
     * @return array
     */
    public function getUpdateForumsForForm(Forum $forum): array;

    /**
     * @param  User  $context
     * @param  array $orderIds
     * @return bool
     */
    public function order(User $context, array $orderIds): bool;

    /**
     * @param  User  $context
     * @param  int   $id
     * @param  bool  $closed
     * @return Forum
     */
    public function close(User $context, int $id, bool $closed): ?Forum;

    /**
     * @param  int $level
     * @return int
     */
    public function countActiveForumByLevel(int $level): int;
}
