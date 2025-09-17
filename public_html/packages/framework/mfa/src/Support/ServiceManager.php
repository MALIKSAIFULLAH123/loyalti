<?php

namespace MetaFox\Mfa\Support;

use InvalidArgumentException;
use MetaFox\Mfa\Contracts\ServiceInterface;
use MetaFox\Mfa\Contracts\ServiceManagerInterface;
use MetaFox\Mfa\Models\Service;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Mfa\Support\Services\Authenticator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ServiceManager.
 */
class ServiceManager implements ServiceManagerInterface
{
    public function __construct(protected ServiceRepositoryInterface $repository)
    {
    }

    public function get(string $name): ServiceInterface
    {
        $service = $this->repository->getServiceByName($name);
        if (!$service instanceof Service) {
            throw new InvalidArgumentException($name);
        }

        /** @var ?ServiceInterface $provider */
        $handler = resolve($service->service_class, ['service' => $service]);

        if (!$handler instanceof ServiceInterface) {
            throw new ServiceNotFoundException($service->service_class);
        }

        return $handler;
    }
}
