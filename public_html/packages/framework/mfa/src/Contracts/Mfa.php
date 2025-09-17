<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Form\AbstractForm;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Interface Mfa.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface Mfa
{
    /**
     * service.
     *
     * @param string $service
     *
     * @return ServiceInterface
     */
    public function service(string $service): ServiceInterface;

    /**
     * getAllowedServices.
     *
     * @return array<mixed>
     */
    public function getAllowedServices(): array;

    /**
     * getAllowedAction.
     *
     * @return array<mixed>
     */
    public function getAllowedAction(): array;

    /**
     * initSetup.
     *
     * @param User   $user
     * @param string $service
     *
     * @return UserService
     */
    public function initSetup(User $user, string $service): UserService;

    /**
     * loadSetupForm.
     *
     * @param UserService $userService
     * @param string      $resolution
     *
     * @return AbstractForm
     */
    public function loadSetupForm(UserService $userService, string $resolution = 'web'): AbstractForm;

    /**
     * @param UserService $userService
     * @param string      $resolution
     *
     * @return AbstractForm
     */
    public function loadPasswordForm(UserService $userService, string $resolution = 'web'): AbstractForm;

    /**
     * loadAuthForm.
     *
     * @param string $mfaToken
     * @param string $service
     * @param string $resolution
     *
     * @return AbstractForm
     */
    public function loadAuthForm(string $mfaToken, string $service, string $resolution = 'web'): AbstractForm;

    /**
     * loadServiceSelectionForm.
     *
     * @param string $mfaToken
     * @param string $resolution
     *
     * @return AbstractForm
     */
    public function loadServiceSelectionForm(string $mfaToken, string $resolution = 'web'): AbstractForm;

    /**
     * activate.
     *
     * @param User         $user
     * @param string       $service
     * @param array<mixed> $params
     *
     * @return UserService
     */
    public function activate(User $user, string $service, array $params = []): UserService;

    /**
     * deactivate.
     *
     * @param User   $user
     * @param string $service
     *
     * @return void
     */
    public function deactivate(User $user, string $service);

    /**
     * authenticate login request.
     *
     * @param FormRequest $request
     *
     * @return bool
     */
    public function authenticate(FormRequest $request);

    /**
     * resendVerificationAuth.
     *
     * @param FormRequest $request
     *
     * @return bool
     */
    public function resendVerificationAuth(FormRequest $request): bool;

    /**
     * resendVerificationSetup.
     *
     * @param User        $user
     * @param FormRequest $request
     *
     * @return bool
     */
    public function resendVerificationSetup(User $user, FormRequest $request): bool;

    /**
     * hasMfaEnabled.
     *
     * @param User $user
     *
     * @return bool
     */
    public function hasMfaEnabled(User $user): bool;

    /**
     * hasMfaServiceEnabled.
     *
     * @param User   $user
     * @param string $service
     *
     * @return bool
     */
    public function hasMfaServiceEnabled(User $user, string $service): bool;

    /**
     * hasConfirmPassword.
     *
     * @param User $user
     *
     * @return bool
     */
    public function hasConfirmPassword(User $user): bool;

    /**
     * verify user login using the $mfaToken.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthenticated(User $user, string $mfaToken): bool;

    /**
     * requestMfaToken.
     *
     * @param User $user
     *
     * @return string
     */
    public function requestMfaToken(User $user, string $resolution = MetaFoxConstant::RESOLUTION_WEB): string;

    /**
     * @return array
     */
    public function getAuthQueryParamsAttribute(): array;
}
