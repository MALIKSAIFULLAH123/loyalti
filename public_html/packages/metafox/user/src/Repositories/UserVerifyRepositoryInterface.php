<?php

namespace MetaFox\User\Repositories;

use MetaFox\User\Models\UserVerify;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface UserVerify.
 * @mixin BaseRepository
 */
interface UserVerifyRepositoryInterface
{
    /**
     * @param  User            $user
     * @param  string          $action
     * @param  string          $verifiable
     * @param  string          $code
     * @return UserVerify|null
     */
    public function generate(User $user, string $action, string $verifiable, string $code): ?UserVerify;

    /**
     * @param  string $action
     * @return string
     */
    public function getVerifyCode(string $action): string;

    /**
     * @param UserVerify|null $verify
     */
    public function commonVerify(?UserVerify $verify): void;

    /**
     * @param User   $user
     * @param string $action
     */
    public function checkResend(User $user, string $action): void;

    /**
     * @param User   $user
     * @param string $verifiable
     */
    public function sendVerificationEmail(User $user, string $verifiable): void;

    /**
     * @param User   $user
     * @param string $verifiable
     */
    public function sendVerificationPhoneNumber(User $user, string $verifiable): void;

    /**
     * @param User   $user
     * @param string $action
     */
    public function invalidatePendingVerify(User $user, string $action);

    public function cleanupPending();

    /**
     * @param  string $code
     * @param  string $action
     * @return string
     */
    public function addSuffixCode(string $code, string $action): string;

    /**
     * @param  User   $user
     * @param  string $action
     * @return int
     */
    public function getRemainingTime(User $user, string $action): int;

    /**
     * @param array $params
     */
    public function resend(array $params): void;

    /**
     * @deprecated Need remove for some next version
     * @param User   $user
     * @param string $action
     * @param string $verifiableValue
     */
    public function resendLink(User $user, string $action, string $verifiableValue): void;

    /**
     * @param  string|null $phoneNumber
     * @param  array       $attributes
     * @return bool
     */
    public function mustVerifyPhoneNumber(?string $phoneNumber, array $attributes): bool;

    /**
     * @param  string|null $email
     * @param  array       $attributes
     * @return bool
     */
    public function mustVerifyEmail(?string $email, array $attributes): bool;
}
