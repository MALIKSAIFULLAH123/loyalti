<?php

namespace MetaFox\User\Support\Verify\Action;

use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\User\Contracts\Support\ActionServiceInterface;
use MetaFox\User\Contracts\Support\ActionServiceManagerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ActionServiceManager implements ActionServiceManagerInterface
{
    public function get(string $service, string $name): ActionServiceInterface
    {
        $service = $this->resolveByDriver($service, $name);

        if (!$service instanceof ActionServiceInterface) {
            throw new ServiceNotFoundException($name);
        }

        return $service;
    }

    public function getAllByService(string $service): array
    {
        $type = "user_verify.action_service.$service";

        return resolve(DriverRepositoryInterface::class)
            ->getDrivers($type, null, $service)->toArray();
    }

    protected function resolveByDriver(string $service, string $name): ?ActionServiceInterface
    {
        $type = "user_verify.action_service.$service";

        [, $class] = resolve(DriverRepositoryInterface::class)->loadDriver($type, $name);
        if (empty($class) || !class_exists($class)) {
            return null;
        }

        return resolve($class);
    }
}
