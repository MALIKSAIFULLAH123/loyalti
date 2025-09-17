<?php

namespace MetaFox\Mfa\Support;

use Carbon\Carbon;
use MetaFox\Mfa\Contracts\MfaEnforcer as ContractsMfaEnforcer;
use MetaFox\Mfa\Models\EnforceRequest;
use MetaFox\Mfa\Models\Service;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Repositories\EnforceRequestRepositoryInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class MfaEnforcer.
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MfaEnforcer implements ContractsMfaEnforcer
{
    public function __construct(
        private EnforceRequestRepositoryInterface $enforceRequestRepository,
        private UserServiceRepositoryInterface $userServiceRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function process(User $user): void
    {
        // already have one active request, let skip it for now
        $activeRequest = $this->enforceRequestRepository->getActiveRequest($user);
        if ($activeRequest instanceof EnforceRequest) {
            return;
        }

        if (Mfa::hasMfaEnabled($user)) {
            return;
        }

        if (!$this->shouldRequire($user)) {
            return;
        }

        $timeout = Settings::get('mfa.enforce_mfa_timeout', 0);
        $this->enforceRequestRepository->createRequest($user, [
            'due_at' => $timeout ? Carbon::now()->addDays($timeout) : null,
        ]);
    }

    public function validate(User $user): void
    {
        $activeRequest = $this->enforceRequestRepository->getActiveRequest($user);
        if (!$activeRequest instanceof EnforceRequest) {
            return;
        }

        if (!$activeRequest->isOverdue()) {
            return;
        }

        if (!Mfa::hasMfaEnabled($user)) {
            // overdue
            $this->onRequestOverdue($activeRequest);
            return;
        }

        $activeRequest->onCancelled();
    }

    public function onUserServiceActivated(UserService $service): void
    {
        $user = $service->user;
        if (!$user instanceof User) {
            return;
        }

        $activeRequest = $this->enforceRequestRepository->getActiveRequest($user);
        if (!$activeRequest instanceof EnforceRequest) {
            return;
        }

        $activeRequest->onSuccess();
    }

    public function onEnforcerDisabled(): void
    {
        // deactivate all active request
        EnforceRequest::query()->where('is_active', 1)->update([
            'is_active' => 0,
            'enforce_status' => EnforceRequest::STATUS_CANCELLED
        ]);
    }

    public function onRequestOverdue(EnforceRequest $enforceRequest): void
    {
        $user = $enforceRequest->user;
        $adminUser = $this->userRepository->getSuperAdmin();
        $userService = null;

        if (!$user instanceof User || !$adminUser instanceof User) {
            return;
        }

        $userService = $this->getServiceToEnforce($user);
        if ($userService instanceof UserService) {
            $userService->onActivated();
            $enforceRequest->onForced();
            return;
        }

        // for security reason, user should be banned if there isn't any possible way
        $this->userRepository->banUser($adminUser, $user, 0, $user->roleId(), __p('mfa::phrase.enforce_overdue_message'));
        $enforceRequest->onBlocked();
    }

    private function shouldRequire(User $user): bool
    {
        if (!Settings::get('mfa.enforce_mfa', false)) {
            return false;
        }

        return match (Settings::get('mfa.enforce_mfa_targets')) {
            'all' => true,
            'roles' => in_array($user->roleId(), Settings::get('mfa.enforce_mfa_roles', [])),
            default => false,
        };
    }

    private function getServiceToEnforce(User $user): ?UserService
    {
        if (!empty($user->email)) {
            $userService = $this->userServiceRepository->getService($user, Service::EMAIL_SERVICE);

            return $userService instanceof UserService ? $userService : $this->userServiceRepository->createService($user, Service::EMAIL_SERVICE, [
                'value' => ''
            ]);
        }

        if (!empty($user->phone_number)) {
            $userService = $this->userServiceRepository->getService($user, Service::SMS_SERVICE);

            return $userService instanceof UserService ? $userService : $this->userServiceRepository->createService($user, Service::SMS_SERVICE, [
                'value' => ''
            ]);
        }

        return null;
    }
}
