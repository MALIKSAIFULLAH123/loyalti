<?php

namespace MetaFox\User\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User as ContractsUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User;

/**
 * Interface UserAdminRepositoryInterface.
 * @mixin AbstractRepository
 * @mixin CollectTotalItemStatTrait
 */
interface UserAdminRepositoryInterface
{
    /**
     * @param ContractsUser        $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     * @return User
     */
    public function updateUser(ContractsUser $context, int $id, array $attributes): User;

    /**
     * @param ContractsUser $context
     * @param ContractsUser $user
     * @param int           $roleId
     * @return bool
     */
    public function moveRole(ContractsUser $context, ContractsUser $user, int $roleId): bool;

    /**
     * @param ContractsUser $context
     * @param User          $user
     * @return bool
     */
    public function verifyUser(ContractsUser $context, User $user): bool;

    /**
     * @param ContractUser         $context
     * @param array<string, mixed> $attributes
     * @return LengthAwarePaginator
     */
    public function viewUsers(ContractsUser $context, array $attributes): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $attributes
     * @return Builder
     */
    public function buildQueryViewUsers(array $attributes): Builder;

    /**
     * @param ContractsUser $context
     * @param ContractsUser $user
     * @return bool
     */
    public function processMailing(ContractsUser $context, ContractsUser $user): bool;

    /**
     * @param ContractsUser $context
     * @param array         $userIds
     * @return void
     */
    public function batchProcessMailing(ContractsUser $context, array $userIds = []): void;

    /**
     * @param ContractsUser $context
     * @param array         $attributes
     * @return void
     */
    public function processMailingAll(ContractsUser $context, array $attributes): void;

    /**
     * @param ContractsUser $context
     * @param array         $attributes
     * @return void
     */
    public function logoutAllUsers(ContractsUser $context, array $attributes): void;
}
