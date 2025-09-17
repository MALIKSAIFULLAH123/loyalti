<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasPrivacyMember.
 *
 * @description If Contract User has members, use this class.
 */
interface HasPrivacyMember
{
    /**
     * Check if a user is a member of this Contract User or not.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isMember(User $user): bool;

    /**
     * Check if a user is a admin of this Contract User or not.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAdmin(User $user): bool;

    /**
     * Check if a user is a admin of this Contract User or not.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isModerator(User $user): bool;

    /**
     * @param  User        $user
     * @return string|null
     */
    public function getRoleLabel(User $user): ?string;
}
