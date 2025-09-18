<?php

namespace MetaFox\LiveStreaming\Contracts;

use Illuminate\Support\Collection;
use MetaFox\LiveStreaming\Models\StreamingService;
use MetaFox\Platform\Contracts\VideoServiceInterface;

interface ServiceManagerInterface
{
    /**
     * @return string
     */
    public function getDefaultServiceName(): string;

    /**
     * @param  bool                  $throw
     * @return StreamingService|bool
     */
    public function getDefaultService(bool $throw = false): StreamingService|null;

    /**
     * @return Collection<StreamingService>
     */
    public function getAllActiveServices(): Collection;

    /**
     * @param  string                $driver
     * @param  bool                  $throw
     * @return StreamingService|bool
     */
    public function getStreamingServiceByDriver(string $driver, bool $throw = false): StreamingService|null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getServicesForForm(): array;

    /**
     * @param  bool                       $throw
     * @return VideoServiceInterface|bool
     */
    public function getDefaultServiceProvider(bool $throw = false): VideoServiceInterface|null;
}
