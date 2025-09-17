<?php

namespace MetaFox\LiveStreaming\Support;

use Illuminate\Support\Collection;
use MetaFox\LiveStreaming\Contracts\ServiceManagerInterface;
use MetaFox\LiveStreaming\Models\StreamingService;
use MetaFox\LiveStreaming\Repositories\StreamingServiceRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Contracts\VideoServiceInterface;

class ServiceManager implements ServiceManagerInterface
{
    private StreamingServiceRepositoryInterface $serviceRepository;

    public function __construct(StreamingServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @return StreamingServiceRepositoryInterface
     */
    public function getServiceRepository(): StreamingServiceRepositoryInterface
    {
        return $this->serviceRepository;
    }

    public function getDefaultService(bool $throw = false): StreamingService|null
    {
        $defaultService = $this->getDefaultServiceName();
        $service        = $this->getServiceRepository()
            ->getModel()
            ->newModelQuery()
            ->where('driver', '=', $defaultService)
            ->first();

        if (!$service instanceof StreamingService) {
            return $throw ? abort(400, __p('livestreaming::phrase.no_active_streaming_service')) : null;
        }

        return $service;
    }

    public function getStreamingServiceByDriver(string $driver, bool $throw = false): StreamingService|null
    {
        $service = $this->getServiceRepository()
            ->getModel()
            ->newModelQuery()
            ->where('driver', $driver)
            ->first();

        if (!$service instanceof StreamingService) {
            return $throw ? abort(400, __p('livestreaming::phrase.no_active_streaming_service')) : null;
        }

        return $service;
    }

    public function getAllActiveServices(): Collection
    {
        return $this->getServiceRepository()
            ->getModel()
            ->newModelQuery()
            ->where('is_active', 1)
            ->get()
            ->collect();
    }

    /**
     * @return array<int, array<string, mixed>>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getServicesForForm(): array
    {
        return $this->getAllActiveServices()
            ->map(function (StreamingService $service, $key) {
                return [
                    'label' => $service->name,
                    'value' => $service->driver,
                ];
            })->toArray();
    }

    public function getDefaultServiceName(): string
    {
        return Settings::get('livestreaming.streaming_service');
    }

    public function getDefaultServiceProvider(bool $throw = false): VideoServiceInterface|null
    {
        $defaultService = $this->getDefaultService($throw);

        return $defaultService ? resolve($defaultService->service_class, ['moduleId' => 'livestreaming', 'throw' => $throw]) : null;
    }
}
