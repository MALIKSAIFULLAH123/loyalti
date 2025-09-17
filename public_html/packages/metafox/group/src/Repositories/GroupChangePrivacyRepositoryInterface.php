<?php

namespace MetaFox\Group\Repositories;

use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;

interface GroupChangePrivacyRepositoryInterface
{
    /**
     * @param Group $group
     * @param User  $user
     * @param array $attributes
     * @return bool
     */
    public function createRequest(Group $group, User $user, array $attributes): bool;

    /**
     * @param User $user
     * @param int  $groupId
     * @return bool
     */
    public function cancelRequest(User $user, int $groupId): bool;

    /**
     * @param int $id
     * @return void
     */
    public function sentNotificationWhenPending(int $id): void;

    /**
     * @param Group $group
     * @return bool
     */
    public function isPendingChangePrivacy(Group $group): bool;

    /**
     * @param int $id
     * @return void
     */
    public function sentNotificationWhenSuccess(int $id): void;

    /**
     * @param User   $user
     * @param Group  $group
     * @param string $privacyType
     * @return void
     */
    public function updatePrivacyGroup(User $user, Group $group, string $privacyType): void;
}
