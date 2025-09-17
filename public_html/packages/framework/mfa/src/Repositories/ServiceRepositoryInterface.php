<?php

namespace MetaFox\Mfa\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Mfa\Models\Service;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Service.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ServiceRepositoryInterface
{
    public function viewServices(User $user): Collection;

    public function getAvailableServices(): Collection;

    public function getServiceByName(string $name): ?Service;

    public function isServiceAvailable(string $name): bool;
}
