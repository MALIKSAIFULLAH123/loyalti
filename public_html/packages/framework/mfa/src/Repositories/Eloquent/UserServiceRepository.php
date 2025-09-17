<?php

namespace MetaFox\Mfa\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use MetaFox\Mfa\Contracts\ServiceInterface;
use MetaFox\Mfa\Contracts\ServiceManagerInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserServiceRepository.
 */
class UserServiceRepository extends AbstractRepository implements UserServiceRepositoryInterface
{
    public function model()
    {
        return UserService::class;
    }

    private function serviceRepository(): ServiceRepositoryInterface
    {
        return resolve(ServiceRepositoryInterface::class);
    }

    public function getService(User $user, string $service): ?UserService
    {
        if (!$this->serviceRepository()->isServiceAvailable($service)) {
            return null;
        }

        return $this->getModel()->newQuery()
            ->where('service', $service)
            ->where('user_id', $user->userId())
            ->where('user_type', $user->userType())
            ->first();
    }

    public function getActivatedServices(User $user): Collection
    {
        return $this->getModel()->newQuery()
            ->where('is_active', 1)
            ->where('user_id', $user->userId())
            ->where('user_type', $user->userType())
            ->get();
    }

    public function getActivatedServicesForForm(User $user, bool $checkReadyConfiguration = false): array
    {
        $options = [];

        foreach ($this->getActivatedServices($user) as $service) {
            $handler = resolve(ServiceManagerInterface::class)->get($service->service);
            if (!$handler instanceof ServiceInterface) {
                continue;
            }

            if ($checkReadyConfiguration && !$handler->isConfigurable($user)) {
                continue;
            }

            $options[] = [
                'label'       => $handler->toTitle(),
                'description' => $handler->toDescription(),
                'value'       => $service->service,
            ];
        }

        return $options;
    }

    public function createService(User $user, string $service, array $params = []): ?UserService
    {
        if (!$this->serviceRepository()->isServiceAvailable($service)) {
            return null;
        }

        $value = Arr::get($params, 'value');
        $extra = Arr::get($params, 'extra', []);

        if (null === $value) {
            return null;
        }

        /** @var UserService $userService */
        $userService = $this->getModel()->create([
            'service'   => $service,
            'user_id'   => $user->userId(),
            'user_type' => $user->userType(),
            'value'     => $value,
            'extra'     => $extra,
        ]);

        return $userService;
    }

    public function removeServices(User $user, string $service)
    {
        $userService = $this->getModel()->newQuery()
            ->where('service', $service)
            ->where('user_id', $user->userId())
            ->where('user_type', $user->userType())
            ->first();

        if ($userService instanceof UserService) {
            $userService->delete();
        }
    }

    public function verifySetup(string $service, string $value): bool
    {
        if (empty($service)) {
            throw new InvalidArgumentException();
        }

        return !$this->getModel()
            ->newQuery()
            ->where('service', $service)
            ->where('value', Crypt::encrypt($value))
            ->exists();
    }

    public function isServiceActivated(User $user, string $service): bool
    {
        if (!$this->serviceRepository()->isServiceAvailable($service)) {
            return false;
        }

        $userService = $this->getService($user, $service);

        return (bool) $userService?->is_active;
    }

    public function deleteServicesByUserId(int $userId)
    {
        $this->deleteWhere([
            'user_id' => $userId,
        ]);
    }
}
