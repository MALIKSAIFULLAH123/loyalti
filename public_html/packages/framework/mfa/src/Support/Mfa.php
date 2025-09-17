<?php

namespace MetaFox\Mfa\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Mfa\Contracts\Mfa as ContractsMfa;
use MetaFox\Mfa\Contracts\ServiceInterface;
use MetaFox\Mfa\Contracts\ServiceManagerInterface;
use MetaFox\Mfa\Models\UserAuthToken;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Mfa\Policies\UserServicePolicy;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Mfa\Repositories\UserAuthTokenRepositoryInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Repositories\UserVerifyCodeRepositoryInterface;
use MetaFox\Mfa\Support\Security\BruteForceMfaProtection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Support\Facades\UserAuth;
use RuntimeException;

/**
 * Class Mfa.
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mfa implements ContractsMfa
{
    public const MFA_REDIRECT_URL = '/mfa/authenticate';

    public function __construct(
        protected UserServiceRepositoryInterface    $userServiceRepository,
        protected ServiceRepositoryInterface        $serviceRepository,
        protected UserVerifyCodeRepositoryInterface $userVerifyCodeRepository,
        protected UserAuthTokenRepositoryInterface  $userAuthTokenRepository,
        protected DriverRepositoryInterface         $driverRepository
    ) {
    }

    public function service(string $service): ServiceInterface
    {
        return resolve(ServiceManagerInterface::class)->get($service);
    }

    public function getAllowedServices(): array
    {
        return $this->serviceRepository
            ->getAvailableServices()
            ->pluck('name')
            ->toArray();
    }

    public function getAllowedAction(): array
    {
        return [UserVerifyCode::SETUP_ACTION, UserVerifyCode::AUTH_ACTION];
    }

    public function initSetup(User $user, string $service): UserService
    {
        policy_authorize(UserServicePolicy::class, 'setup', $user, $service);

        $config = $this->initService($user, $service);

        $this->userServiceRepository->removeServices($user, $service);
        $userService = $this->userServiceRepository->createService($user, $service, $config);
        if (!$userService instanceof UserService) {
            throw new RuntimeException(__p('mfa::phrase.failed_to_initialize_service_setup', ['service' => $service]));
        }

        return $userService;
    }

    public function loadSetupForm(UserService $userService, string $resolution = 'web'): AbstractForm
    {
        $service = $userService->service;
        $handler = $this->service($service);

        if (!$handler->isConfigurable($userService->user)) {
            throw new RuntimeException(__p('mfa::phrase.error_missing_config_required'));
        }

        return $handler->setupForm($userService, $resolution);
    }

    public function loadAuthForm(string $mfaToken, string $service, string $resolution = 'web'): AbstractForm
    {
        $userAuthToken = $this->userAuthTokenRepository->findByTokenValue($mfaToken);
        $this->validateAuthToken($userAuthToken);

        $bruteForceMfa = $this->bruteForceMfaService();
        $bruteForceMfa->verify(['user_id' => $userAuthToken->userId()]);

        $handler = $this->service($service);
        if (!$handler->isConfigurable($userAuthToken->user)) {
            throw new RuntimeException(__p('mfa::phrase.error_missing_config_required'));
        }

        return $handler->authForm($userAuthToken, $resolution);
    }

    public function loadServiceSelectionForm(string $mfaToken, string $resolution = 'web'): AbstractForm
    {
        $userAuthToken = $this->userAuthTokenRepository->findByTokenValue($mfaToken);
        $this->validateAuthToken($userAuthToken);

        return $this->loadForm($userAuthToken, 'mfa.user_service.choose_authentication_service_form', $resolution);
    }

    protected function validateAuthToken($userAuthToken): void
    {
        if (!$userAuthToken || $userAuthToken->isExpired()) {
            throw new RuntimeException(__p('mfa::phrase.the_mfa_token_has_been_expired'));
        }
    }

    public function activate(User $user, string $service, array $params = []): UserService
    {
        policy_authorize(UserServicePolicy::class, 'setup', $user, $service);

        $userService = $this->userServiceRepository->getService($user, $service);
        if (!$userService instanceof UserService) {
            throw new RuntimeException(__p('mfa::phrase.the_service_hasn_t_been_initialized_yet', ['service' => $service]));
        }

        $handler = $this->service($service);
        if (!$handler->verifyActivation($userService, $params)) {
            throw new RuntimeException(__p('mfa::phrase.could_not_verify_the_mfa_service', ['service' => $service]));
        }

        return $userService->onActivated();
    }

    public function deactivate(User $user, string $service)
    {
        policy_authorize(UserServicePolicy::class, 'remove', $user, $service);

        $this->userServiceRepository->removeServices($user, $service);
    }

    public function authenticate(FormRequest $request)
    {
        $params        = $request->validated();
        $returnUrl     = Arr::get($params, 'return_url', '/');
        $remember      = Arr::get($params, 'remember', 0);
        $userAuthToken = $this->getUserAuthToken($params);
        $userService   = $this->getUserService($params);
        $userId        = $userService->userId();

        $bruteForceMfa = $this->bruteForceMfaService();
        $bruteForceMfa->verify(['user_id' => $userId]);

        if (!$this->verifyByService($userService, $params)) {
            $bruteForceMfa->process(['user_id' => $userId]);
            $bruteForceMfa->verify(['user_id' => $userId]);

            throw new AuthenticationException(__p('mfa::phrase.the_authentication_code_is_not_valid'));
        }

        $userService->onAuthenticated();
        $userAuthToken->onAuthenticated();
        $bruteForceMfa->clearCache($userId);

        $response = UserAuth::authorize($request->merge([
            'username' => $userAuthToken->user->user_name,
        ]));

        if ($response->isOk()) {
            $data = json_decode($response->getContent(), true);
            if ($userAuthToken->resolution == MetaFoxConstant::RESOLUTION_ADMIN) {
                $returnUrl = match ($returnUrl === null) {
                    true  => '/admincp',
                    false => '/admincp' . $returnUrl
                };
            }

            if ($returnUrl !== null) {
                Arr::set($data, 'returnUrl', $returnUrl);
            }

            if ($remember) {
                Arr::set($data, 'remember', (bool) $remember);
            }

            $response->setContent(json_encode($data));
        }

        return $response;
    }

    public function resendVerificationAuth(FormRequest $request): bool
    {
        $params      = $request->validated();
        $userService = $this->getUserService($params);

        if (!$userService instanceof UserService) {
            throw new RuntimeException(__p('mfa::phrase.the_service_hasn_t_been_initialized_yet', ['service' => $params['service']]));
        }

        return $this->resendVerification($userService, $params['action']);
    }

    public function resendVerificationSetup(User $user, FormRequest $request): bool
    {
        $params      = $request->validated();
        $userService = $this->userServiceRepository->getService($user, $params['service']);

        if (!$userService instanceof UserService) {
            throw new RuntimeException(__p('mfa::phrase.the_service_hasn_t_been_initialized_yet', ['service' => $params['service']]));
        }

        return $this->resendVerification($userService, $params['action']);
    }

    protected function resendVerification(UserService $userService, string $action): bool
    {
        $service = $userService->service;
        $handler = $this->service($service);

        if (!$handler->resendVerification($userService, $action)) {
            $this->handleResendVerificationFailure($userService, $handler, $service, $action);
        }

        return true;
    }

    protected function handleResendVerificationFailure(UserService $userService, ServiceInterface $handler, string $service, string $action): void
    {
        $userVerifyCode = $this->userVerifyCodeRepository
            ->getUserVerifyCodeByUser($userService->user, $service, $action);

        $remainingTime = $handler->getRemainingTime($userVerifyCode);
        $message       = __p('mfa::phrase.must_wait_to_resend_verification', ['duration' => $remainingTime]);

        abort(403, $message);
    }

    protected function getUserAuthToken(array $params): UserAuthToken
    {
        $mfaToken      = Arr::get($params, 'password', '');
        $userAuthToken = $this->userAuthTokenRepository->findByTokenValue($mfaToken);

        if (!$userAuthToken) {
            throw new RuntimeException(__p('mfa::phrase.the_token_does_not_exist'));
        }

        $user = $userAuthToken->user;

        if (!$user || $userAuthToken->isExpired()) {
            throw new RuntimeException(__p('mfa::phrase.the_token_is_no_longer_valid'));
        }

        return $userAuthToken;
    }

    protected function getUserService(array $params): UserService
    {
        $service = Arr::get($params, 'service');

        $userAuthToken = $this->getUserAuthToken($params);
        $user          = $userAuthToken->user;

        return $this->userServiceRepository->getService($user, $service);
    }

    public function hasMfaEnabled(User $user): bool
    {
        return $this->userServiceRepository
            ->getActivatedServices($user)
            ->isNotEmpty();
    }

    public function hasConfirmPassword(User $user): bool
    {
        return Settings::get('mfa.confirm_password') && $user->getAuthPassword();
    }

    public function hasMfaServiceEnabled(User $user, string $service): bool
    {
        return $this->userServiceRepository
            ->isServiceActivated($user, $service);
    }

    public function isAuthenticated(User $user, string $mfaToken): bool
    {
        $userAuthToken = $this->userAuthTokenRepository->findByTokenValue($mfaToken);
        if (!$userAuthToken) {
            return false;
        }

        if (!$userAuthToken->isUser($user)) {
            return false;
        }

        return $userAuthToken->isAuthenticated();
    }

    public function requestMfaToken(User $user, string $resolution = MetaFoxConstant::RESOLUTION_WEB): string
    {
        return $this->userAuthTokenRepository->generateTokenForUser($user, 5, $resolution)->value;
    }

    /**
     * verifyByService.
     *
     * @param UserService  $userService
     * @param array<mixed> $params
     *
     * @return bool
     */
    private function verifyByService(UserService $userService, array $params = []): bool
    {
        $service = $userService->service;
        $handler = $this->service($service);

        return $handler->verifyAuth($userService, $params);
    }

    /**
     * initService.
     *
     * @param User   $user
     * @param string $service
     *
     * @return array<mixed>
     */
    private function initService(User $user, string $service): array
    {
        $handler = $this->service($service);

        do {
            $setup = $handler->setup($user, $service);
            $value = Arr::get($setup, 'value', '');
        } while (!$this->userServiceRepository->verifySetup($service, $value));

        return $setup;
    }

    public function loadPasswordForm(UserService $userService, string $resolution = 'web'): AbstractForm
    {
        return $this->loadForm($userService, 'mfa.user_service.confirm_password', $resolution);
    }

    protected function loadForm($resource, $driverName, $resolution = 'web'): AbstractForm
    {
        $driver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, $driverName, $resolution);

        $form = resolve($driver, ['resource' => $resource]);
        if (!$form instanceof AbstractForm) {
            throw new RuntimeException(__p('mfa::phrase.could_not_load_form'));
        }

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot']);
        }

        return $form;
    }

    protected function bruteForceMfaService(): BruteForceMfaProtection
    {
        return new BruteForceMfaProtection();
    }

    public function getAuthQueryParamsAttribute(): array
    {
        return ['return_url', 'remember'];
    }
}
