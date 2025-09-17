<?php

namespace MetaFox\Payment\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Gateway.
 *
 * @mixin BaseRepository
 * @method Gateway getModel()
 * @method Gateway find($id, $columns = ['*'])
 */
interface GatewayRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return Gateway
     * @throws AuthorizationException
     */
    public function viewGateway(User $context, int $id): Gateway;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewGateways(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Gateway
     * @throws AuthorizationException
     */
    public function updateGateway(User $context, int $id, array $attributes): Gateway;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $isActive
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updateActive(User $context, int $id, int $isActive): bool;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $isTestMode
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function updateTestMode(User $context, int $id, int $isTestMode): bool;

    /**
     * @return Collection
     */
    public function getActiveGateways(): Collection;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return Collection
     */
    public function getGatewaysForForm(User $context, array $params = []): Collection;

    /**
     * @param  string       $service
     * @return Gateway|null
     */
    public function getGatewayByService(string $service): ?Gateway;

    /**
     * @return Collection
     */
    public function getConfigurationGateways(): Collection;

    /**
     * @param array<mixed> $configs
     */
    public function setupPaymentGateways(array $configs = []): void;

    /**
     * @return array
     */
    public function getGatewaySearchOptions(): array;

    /**
     * @return Collection
     */
    public function getBuyerConfigurableGateways(): Collection;
}
