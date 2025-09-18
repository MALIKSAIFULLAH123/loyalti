<?php

namespace MetaFox\Video\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\VideoServiceInterface;

interface ProviderManagerInterface
{
    /**
     * @return VideoServiceInterface
     */
    public function getDefaultServiceClass(): VideoServiceInterface;

    /**
     * @param  string                $driver
     * @return VideoServiceInterface
     */
    public function getVideoServiceClassByDriver(string $driver): VideoServiceInterface;

    /**
     * @return bool
     */
    public function checkReadyService(): bool;

    /**
     * @return array
     */
    public function getServicesOptions(): array;

    /**
     * @param  User               $context
     * @param  array              $params
     * @return EloquentCollection
     */
    public function viewServices(User $context, array $params = []): EloquentCollection;

    /**
     * Used for determining the timeout time for a job.
     *
     * @return int
     */
    public function getProcessingTimeout(): int;
}
