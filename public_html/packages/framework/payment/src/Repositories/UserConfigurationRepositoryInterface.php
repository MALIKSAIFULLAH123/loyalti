<?php

namespace MetaFox\Payment\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface UserConfiguration.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface UserConfigurationRepositoryInterface
{
    /**
     * @param  int        $userId
     * @param  string     $serviceName
     * @return array|null
     */
    public function getConfiguration(int $userId, string $serviceName): ?array;

    /**
     * @param  int   $userId
     * @param  array $attributes
     * @return bool
     */
    public function updateConfiguration(int $userId, array $attributes): bool;

    /**
     * @param  int   $userId
     * @param  array $attributes
     * @return bool
     */
    public function updateMultipleConfiguration(int $userId, array $attributes): bool;

    /**
     * @param  int  $userId
     * @param  int  $gatewayId
     * @return bool
     */
    public function hasAccess(int $userId, int $gatewayId): bool;
}
