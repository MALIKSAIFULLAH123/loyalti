<?php

namespace MetaFox\Mfa\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserService.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface UserServiceRepositoryInterface
{
    /**
     * createService.
     *
     * @param  User         $user
     * @param  string       $service
     * @param  array<mixed> $params
     * @return UserService
     */
    public function createService(User $user, string $service, array $params = []): ?UserService;

    /**
     * removeServices.
     *
     * @param  User   $user
     * @param  string $service
     * @return void
     */
    public function removeServices(User $user, string $service);

    /**
     * getService.
     *
     * @param  User        $user
     * @param  string      $service
     * @return UserService
     */
    public function getService(User $user, string $service): ?UserService;

    /**
     * getActivatedServices.
     *
     * @param  User       $user
     * @return Collection
     */
    public function getActivatedServices(User $user): Collection;

    /**
     * getActivatedServicesForForm.
     *
     * @param  User  $user
     * @param  bool  $checkReadyConfiguration
     * @return array
     */
    public function getActivatedServicesForForm(User $user, bool $checkReadyConfiguration = false): array;

    /**
     * verifySetup.
     *
     * @param  string $service
     * @param  string $value
     * @return bool
     */
    public function verifySetup(string $service, string $value): bool;

    /**
     * isServiceActivated.
     *
     * @param  User   $user
     * @param  string $service
     * @return bool
     */
    public function isServiceActivated(User $user, string $service): bool;

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteServicesByUserId(int $userId);
}
