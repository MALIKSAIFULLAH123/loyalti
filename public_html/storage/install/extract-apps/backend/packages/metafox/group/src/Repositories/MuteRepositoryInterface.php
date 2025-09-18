<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Mute.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait
 */
interface MuteRepositoryInterface
{
    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     * @return bool
     */
    public function muteInGroup(User $context, int $groupId, array $attributes): bool;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     * @return bool
     */
    public function unmuteInGroup(User $context, int $groupId, int $userId): bool;

    /**
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function deleteMute(int $groupId, int $userId): bool;

    /**
     * @return void
     */
    public function syncUserMuted(): void;

    /**
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function isMuted(int $groupId, int $userId): bool;

    /**
     * @param int $groupId
     * @param int $userId
     */
    public function getUserMuted(int $groupId, int $userId);

    /**
     * @param User  $context
     * @param int   $groupId
     * @param array $attributes
     * @return Paginator
     */
    public function viewMutedUsersInGroup(User $context, int $groupId, array $attributes): Paginator;
}
