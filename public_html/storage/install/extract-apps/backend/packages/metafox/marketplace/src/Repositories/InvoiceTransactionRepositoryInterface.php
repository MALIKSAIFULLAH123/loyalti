<?php

namespace MetaFox\Marketplace\Repositories;

use Illuminate\Support\Enumerable;
use MetaFox\Marketplace\Models\InvoiceTransaction;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface InvoiceTransaction.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface InvoiceTransactionRepositoryInterface
{
    /**
     * @param  array              $attributes
     * @return InvoiceTransaction
     */
    public function createTransaction(array $attributes): InvoiceTransaction;

    /**
     * @param  int  $invoiceId
     * @return void
     */
    public function deleteTransactions(int $invoiceId): void;

    /**
     * @param  User       $context
     * @param  int        $invoiceId
     * @return Enumerable
     */
    public function viewTransactions(User $context, int $invoiceId): Enumerable;
}
