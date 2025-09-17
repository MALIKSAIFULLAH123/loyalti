<?php

namespace MetaFox\Payment\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Order.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface OrderAdminRepositoryInterface
{
    /**
     * @param  User                $context
     * @param  array<string,mixed> $attributes
     * @return Collection
     */
    public function getTransactions(User $context, array $attributes): Collection;

    /**
     * @param  User    $context
     * @param  array   $attributes
     * @return Builder
     */
    public function getTransactionsBuilder(User $context, array $attributes): Builder;

    /**
     * @param array $attributes
     * @return Paginator
     */
    public function viewOrders(array $attributes = []): Paginator;
}
