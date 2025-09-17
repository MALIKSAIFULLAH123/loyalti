<?php

namespace MetaFox\Mfa\Support\Services;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Mfa\Contracts\OTPServiceInterface;
use MetaFox\Mfa\Models\UserAuthToken;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Repositories\UserVerifyCodeRepositoryInterface;
use MetaFox\Mfa\Support\AbstractService;
use MetaFox\Platform\Contracts\User;

/**
 * Class OTPService.
 *
 * @ignore
 * @codeCoverageIgnore
 */
abstract class OTPService extends AbstractService implements OTPServiceInterface
{
    public function setup(User $user, string $service): array
    {
        return ['value' => ''];
    }

    public function setupForm(UserService $userService, ?string $resolution = 'web'): AbstractForm
    {
        $this->beforeSetupForm($userService);

        return parent::setupForm($userService, $resolution);
    }

    public function authForm(UserAuthToken $userAuthToken, ?string $resolution = 'web'): AbstractForm
    {
        $this->beforeAuthForm($userAuthToken, $this->service->name);

        return parent::authForm($userAuthToken, $resolution);
    }

    public function verifyAuth(UserService $userService, array $params = []): bool
    {
        return $this->verifyCode($userService, UserVerifyCode::AUTH_ACTION, Arr::get($params, 'verification_code', ''));
    }

    public function verifyActivation(UserService $userService, array $params = []): bool
    {
        return $this->verifyCode($userService, UserVerifyCode::SETUP_ACTION, Arr::get($params, 'verification_code', ''));
    }

    public function getVerifyCode(): string
    {
        $codeLength = 6;

        return str_pad(random_int(0, str_repeat(9, $codeLength)), $codeLength, '0', STR_PAD_LEFT);
    }

    public function resendVerification(UserService $userService, string $action): bool
    {
        return $this->userVerifyCodeRepository()->resendVerification($userService, $action);
    }

    public function getRemainingTime(UserVerifyCode $userVerifyCode): int
    {
        return $this->userVerifyCodeRepository()->getRemainingTime($userVerifyCode);
    }

    public function send(User $user, string $code): bool
    {
        return true;
    }

    private function beforeSetupForm(UserService $userService): void
    {
        $this->resendVerification($userService, UserVerifyCode::SETUP_ACTION);
    }

    private function beforeAuthForm(UserAuthToken $userAuthToken, ?string $service = null): void
    {
        if (null == $service) {
            return;
        }

        $userService = $this->getUserService($userAuthToken->user, $service);

        if ($userService instanceof UserService) {
            $this->resendVerification($userService, UserVerifyCode::AUTH_ACTION);
        }
    }

    private function verifyCode(UserService $userService, string $action, string $code): bool
    {
        return $this->userVerifyCodeRepository()->verifyCode($userService, $action, $code);
    }

    protected function getUserService(User $user, string $service): ?UserService
    {
        return resolve(UserServiceRepositoryInterface::class)->getService($user, $service);
    }

    protected function userVerifyCodeRepository(): UserVerifyCodeRepositoryInterface
    {
        return resolve(UserVerifyCodeRepositoryInterface::class);
    }
}
