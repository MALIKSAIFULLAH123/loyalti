<?php

namespace MetaFox\Mfa\Repositories;

use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserVerifyCodeRepositoryInterface.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface UserVerifyCodeRepositoryInterface
{
    /**
     * @param  User           $user
     * @param  string         $service
     * @param  string         $action
     * @param  string         $code
     * @return UserVerifyCode
     */
    public function createUserVerifyCode(User $user, string $service, string $action, string $code): UserVerifyCode;

    /**
     * @param  User                $user
     * @param  string              $service
     * @param  string              $action
     * @return UserVerifyCode|null
     */
    public function getUserVerifyCodeByUser(User $user, string $service, string $action): ?UserVerifyCode;

    /**
     * @param  UserService $userService
     * @param  string      $action
     * @return bool
     */
    public function shouldSendVerification(UserService $userService, string $action): bool;

    /**
     * @param  UserService $userService
     * @param  string      $action
     * @param  string      $code
     * @return bool
     */
    public function verifyCode(UserService $userService, string $action, string $code): bool;

    /**
     * @param  UserService $userService
     * @param  string      $action
     * @return bool
     */
    public function resendVerification(UserService $userService, string $action): bool;

    /**
     * @param  User   $user
     * @param  string $service
     * @param  string $action
     * @return bool
     */
    public function sendVerification(User $user, string $service, string $action): bool;

    /**
     * @param  UserVerifyCode $userVerifyCode
     * @return int
     */
    public function getRemainingTime(UserVerifyCode $userVerifyCode): int;
}
