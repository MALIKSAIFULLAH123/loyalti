<?php

namespace MetaFox\LiveStreaming\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\LiveStreaming\Models\StreamingService as Model;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface StreamingService.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface StreamingServiceRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return Paginator
     */
    public function viewServices(User $context, array $params = []): Paginator;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $params
     * @return Model
     */
    public function updateService(User $context, int $id, array $params = []): Model;

    /**
     * @return array<int, mixed>
     */
    public function getServicesOptions(): array;
}
