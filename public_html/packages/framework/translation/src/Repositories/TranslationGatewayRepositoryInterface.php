<?php

namespace MetaFox\Translation\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Translation\Models\TranslationGateway;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Gateway.
 *
 * @mixin BaseRepository
 * @method TranslationGateway getModel()
 * @method TranslationGateway find($id, $columns = ['*'])
 */
interface TranslationGatewayRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewTranslationGateways(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return TranslationGateway
     * @throws AuthorizationException
     */
    public function updateTranslationGateway(User $context, int $id, array $attributes): TranslationGateway;

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
     * @param array<mixed> $configs
     */
    public function setupTranslationGateways($configs): void;
}
