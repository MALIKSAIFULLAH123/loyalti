<?php

namespace MetaFox\EMoney\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\EMoney\Contracts\WithdrawMethodInterface;
use MetaFox\EMoney\Models\WithdrawMethod;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface WithdrawMethod.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface WithdrawMethodRepositoryInterface
{
    /**
     * @return Collection
     */
    public function viewMethods(): Collection;

    /**
     * @param  string $service
     * @param  bool   $active
     * @return bool
     */
    public function activateMethod(string $service, bool $active): bool;
}
