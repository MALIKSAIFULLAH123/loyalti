<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Contracts;

use MetaFox\Form\AbstractForm;
use MetaFox\Mfa\Models\UserAuthToken;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Platform\Contracts\User;

/**
 * Interface ServiceInterface.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface ServiceInterface
{
    /**
     * to the service title.
     *
     * @return string
     */
    public function toTitle(): string;

    /**
     * to the service description.
     *
     * @return string
     */
    public function toDescription(): string;

    /**
     * to the service icon.
     * @param  string $resolution
     * @return string
     */
    public function toIcon(string $resolution = 'web'): string;

    /**
     * setup the service for user.
     *
     * @param  User         $user
     * @param  string       $service
     * @return array<mixed>
     */
    public function setup(User $user, string $service): array;

    /**
     * load the setup form.
     *
     * @param  UserService  $userService
     * @param  string       $resolution
     * @return AbstractForm
     */
    public function setupForm(UserService $userService, ?string $resolution = 'web'): AbstractForm;

    /**
     * isReadyConfiguration.
     *
     * @param  User $user
     * @return bool
     */
    public function isConfigurable(User $user): bool;

    /**
     * load the authenticate form.
     *
     * @param  UserAuthToken $userAuthToken
     * @param  string        $resolution
     * @return AbstractForm
     */
    public function authForm(UserAuthToken $userAuthToken, ?string $resolution = 'web'): AbstractForm;

    /**
     * getRemainingTime.
     *
     * @param  UserVerifyCode $userVerifyCode
     * @return int
     */
    public function getRemainingTime(UserVerifyCode $userVerifyCode): int;

    /**
     * verify in the authentication process.
     *
     * @param  UserService  $userService
     * @param  array<mixed> $params
     * @return bool
     */
    public function verifyAuth(UserService $userService, array $params = []): bool;

    /**
     * verify in the activation process.
     *
     * @param  UserService  $userService
     * @param  array<mixed> $params
     * @return bool
     */
    public function verifyActivation(UserService $userService, array $params = []): bool;

    /**
     * resendVerification.
     *
     * @param  UserService $userService
     * @param  string      $action
     * @return bool
     */
    public function resendVerification(UserService $userService, string $action): bool;

    /**
     * validateField.
     * @return array
     */
    public function validateField(): array;
}
