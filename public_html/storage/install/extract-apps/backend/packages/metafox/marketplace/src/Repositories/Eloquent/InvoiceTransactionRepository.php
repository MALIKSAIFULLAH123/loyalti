<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Support\Enumerable;
use MetaFox\Marketplace\Policies\InvoicePolicy;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Marketplace\Repositories\InvoiceTransactionRepositoryInterface;
use MetaFox\Marketplace\Models\InvoiceTransaction;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InvoiceTransactionRepository.
 */
class InvoiceTransactionRepository extends AbstractRepository implements InvoiceTransactionRepositoryInterface
{
    public function model()
    {
        return InvoiceTransaction::class;
    }

    public function createTransaction(array $attributes): InvoiceTransaction
    {
        $transaction = new InvoiceTransaction();

        $transaction->fill($attributes);

        $transaction->save();

        return $transaction;
    }

    public function deleteTransactions(int $invoiceId): void
    {
        $this->getModel()->newModelQuery()
            ->where([
                'invoice_id' => $invoiceId,
            ])
            ->delete();
    }

    public function viewTransactions(User $context, int $invoiceId): Enumerable
    {
        $invoice = resolve(InvoiceRepositoryInterface::class)->find($invoiceId);

        policy_authorize(InvoicePolicy::class, 'view', $context, $invoice);

        return $this->getModel()->newModelQuery()
            ->with(['gateway'])
            ->where([
                'invoice_id' => $invoice->entityId(),
            ])
            ->get();
    }
}
