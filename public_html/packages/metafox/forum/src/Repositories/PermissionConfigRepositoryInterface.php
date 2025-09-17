<?php

namespace MetaFox\Forum\Repositories;

use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PermissionConfig
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface PermissionConfigRepositoryInterface
{
    /**
     * @param int $forumId
     * @return array
     */
    public function getConfigs(int $forumId): array;

    /**
     * @param User  $user
     * @param Forum $forum
     * @param array $configs
     * @return bool
     */
    public function updateConfigs(User $user, Forum $forum, array $configs): bool;
}
