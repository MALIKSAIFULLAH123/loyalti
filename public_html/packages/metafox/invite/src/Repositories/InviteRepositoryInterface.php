<?php

namespace MetaFox\Invite\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Invite\Models\Invite;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Invite.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @mixin UserMorphTrait;
 */
interface InviteRepositoryInterface
{
    /**
     * @param User  $user
     * @param array $params
     * @return array
     */
    public function createInvites(User $user, array $params): array;

    /**
     * @param array $recipients
     * @return string
     */
    public function getMessageForInviteSuccess(array $recipients): string;

    /**
     * @param User  $user
     * @param User  $owner
     * @param array $params
     * @return Paginator
     */
    public function viewInvites(User $user, User $owner, array $params): Paginator;

    /**
     * @param User    $user
     * @param Builder $query
     * @param array   $params
     * @return Builder
     */
    public function buildQueryViewInvites(User $user, Builder $query, array $params): Builder;

    /**
     * @param User   $user
     * @param string $value
     * @param bool   $checkDuplicate
     * @return Builder
     */
    public function builderQueryInvite(User $user, string $value, bool $checkDuplicate = false): Builder;

    /**
     * @param User $user
     * @param int  $id
     * @return Invite|null
     */
    public function resend(User $user, int $id): ?Invite;

    /**
     * @param User $user
     * @param int  $id
     * @return bool
     */
    public function deleteInvite(User $user, int $id): bool;

    /**
     * @param User  $user
     * @param array $params
     * @return void
     */
    public function batchResend(User $user, array $params): void;

    /**
     * @param User  $user
     * @param array $params
     * @return void
     */
    public function batchDeleted(User $user, array $params): void;

    /**
     * @param string $inviteCode
     * @return Builder
     */
    public function getBuilderByInviteCode(string $inviteCode): Builder;

    /**
     * @param string $code
     * @return Builder
     */
    public function getBuilderByCode(string $code): Builder;
}
