<?php

namespace MetaFox\Marketplace\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface InvoiceRepositoryInterface.
 * @mixin BaseRepository
 */
interface InvoiceAdminRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewInvoices(User $context, array $attributes = []): Paginator;

    /**
     * @param User $context
     * @param int  $id
     * @return Invoice|null
     */
    public function viewInvoice(User $context, int $id): ?Invoice;


    /**
     * @param User $context
     * @param int  $id
     * @return bool
     */
    public function deleteInvoice(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     * @return Invoice
     */
    public function cancelInvoice(User $context, int $id): Invoice;

    /**
     * @param User $context
     * @param int  $invoiceId
     * @return Collection
     */
    public function viewTransactionsInAdminCP(User $context, int $invoiceId): Collection;
}
