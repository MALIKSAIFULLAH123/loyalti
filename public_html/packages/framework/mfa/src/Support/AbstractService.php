<?php

namespace MetaFox\Mfa\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Mfa\Contracts\ServiceInterface;
use MetaFox\Mfa\Models\Service;
use MetaFox\Mfa\Models\UserAuthToken;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use RuntimeException;

/**
 * Class ServiceManager.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class AbstractService implements ServiceInterface
{
    private DriverRepositoryInterface $driverRepository;

    public function __construct(protected Service $service)
    {
        $this->driverRepository = resolve(DriverRepositoryInterface::class);
    }

    public function toTitle(): string
    {
        return __p("mfa::phrase.{$this->service->name}_provider_title");
    }

    public function toDescription(): string
    {
        return __p("mfa::phrase.{$this->service->name}_provider_description");
    }

    public function toIcon(string $resolution = 'web'): string
    {
        return Arr::get($this->service->config, "icon.$resolution", '');
    }

    public function authForm(UserAuthToken $userAuthToken, ?string $resolution = 'web'): AbstractForm
    {
        return $this->initializeForm($userAuthToken, 'auth_form', $resolution);
    }

    public function setupForm(UserService $userService, ?string $resolution = 'web'): AbstractForm
    {
        return $this->initializeForm($userService, 'setup_form', $resolution);
    }

    public function isConfigurable(User $user): bool
    {
        return true;
    }

    public function getRemainingTime(UserVerifyCode $userVerifyCode): int
    {
        return 0;
    }

    public function verifyAuth(UserService $userService, array $params = []): bool
    {
        return true;
    }

    public function verifyActivation(UserService $userService, array $params = []): bool
    {
        return true;
    }

    public function resendVerification(UserService $userService, string $action): bool
    {
        return true;
    }

    public function validateField(): array
    {
        return [];
    }

    private function initializeForm($resource, string $serviceType, ?string $resolution = 'web'): AbstractForm
    {
        $service = $this->service->name;
        $form    = $this->loadForm($resource, "mfa.user_service.{$serviceType}_" . $service, $resolution);

        if (!$form instanceof AbstractForm) {
            throw new RuntimeException(__p('mfa::phrase.could_not_initialize_the_mfa_service', ['service_type' => $serviceType, 'service' => $service]));
        }

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot']);
        }

        return $form;
    }

    private function loadForm(Model $resource, string $driverName, ?string $resolution = 'web'): ?AbstractForm
    {
        $driver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, $driverName, $resolution ?? MetaFoxConstant::RESOLUTION_WEB);

        /* @var ?AbstractForm $form */
        return resolve($driver, ['resource' => $resource]);
    }
}
