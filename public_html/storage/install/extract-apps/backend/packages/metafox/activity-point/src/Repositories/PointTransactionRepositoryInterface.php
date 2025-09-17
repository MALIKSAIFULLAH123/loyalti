<?php

namespace MetaFox\ActivityPoint\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\ActivityPoint\Models\PointTransaction as Transaction;
use MetaFox\ActivityPoint\Support\ActivityPoint as PointSupport;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Interface PointTransactionRepositoryInterface.
 *
 * @method Transaction find($id, $columns = ['*'])
 * @method Transaction getModel()
 */
interface PointTransactionRepositoryInterface
{
    /**
     * @param  User             $user
     * @param  Entity           $model
     * @param  array            $attributes
     * @return Transaction|null
     */
    public function getTransactionByItem(User $user, Entity $model, array $attributes): ?Transaction;

    /**
     * @param  User                $context
     * @param  array<string,mixed> $attributes
     * @return Paginator
     */
    public function viewTransactions(User $context, array $attributes): Paginator;

    /**
     * @param  User        $context
     * @param  int         $id
     * @return Transaction
     */
    public function viewTransaction(User $context, int $id): Transaction;

    /**
     * @param  User                 $context
     * @param  User                 $owner
     * @param  array<string, mixed> $params
     * @return Transaction
     */
    public function createTransaction(User $context, User $owner, array $params): Transaction;

    /**
     * @param  User                $context
     * @param  array<string,mixed> $attributes
     * @return Paginator
     */
    public function viewTransactionsAdmin(User $context, array $attributes): Paginator;

    /**
     * @return array<int, mixed>
     */
    public function getPackageOptions(): array;

    /**
     * @param  string $time
     * @return int
     */
    public function getAdminSentPointByTime(string $time): int;
}
