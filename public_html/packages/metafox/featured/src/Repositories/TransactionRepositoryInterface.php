<?php

namespace MetaFox\Featured\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Models\Transaction;
use MetaFox\Platform\Contracts\Content;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Transaction
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TransactionRepositoryInterface
{
    /**
     * @param Content $content
     * @return bool
     */
    public function handleContentDeleted(Content $content): bool;

    /**
     * @param Item $item
     * @return bool
     */
    public function handleItemDeleted(Item $item): bool;

    /**
     * @param array $attributes
     * @return Transaction
     */
    public function createTransaction(array $attributes): Transaction;

    /**
     * @param array $attributes
     * @return Paginator
     */
    public function viewTransactions(array $attributes = []): Paginator;
}
