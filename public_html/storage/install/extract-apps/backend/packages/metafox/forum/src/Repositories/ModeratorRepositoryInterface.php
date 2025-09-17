<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Moderator.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ModeratorRepositoryInterface
{
    /**
     * @return array
     */
    public function getPerms(): array;

    /**
     * @param int      $userId
     * @param int|null $forumId
     * @param string   $permission
     * @return bool
     */
    public function hasAccess(int $userId, int|null $forumId, string $permission): bool;

    /**
     * @param int $forumId
     * @param bool $toArray
     * @return array|Collection
     */
    public function getForumModerators(int $forumId, bool $toArray = false): array|Collection;

    /**
     * @param User  $context
     * @param Forum $forum
     * @param array $moderatorIds
     * @param array $configParams
     * @return bool
     */
    public function setupModerators(User $context, Forum $forum, array $moderatorIds, array $configParams): bool;

    /**
     * @param User  $user
     * @param array $data
     * @return Collection
     */
    public function searchModerators(User $user, array $data): Collection;
}
