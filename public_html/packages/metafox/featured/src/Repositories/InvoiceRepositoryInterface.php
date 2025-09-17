<?php

namespace MetaFox\Featured\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Item;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Invoice
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface InvoiceRepositoryInterface
{
    /**
     * @param User    $user
     * @param Content $content
     * @param array   $attributes
     * @return Invoice
     */
    public function createInvoiceForFree(User $user, Content $content, array $attributes): Invoice;

    /**
     * @param User    $user
     * @param Content $content
     * @param array   $attributes
     * @return Invoice
     */
    public function createInvoice(User $user, Content $content, array $attributes): Invoice;

    /**
     * @param Order       $order
     * @param string|null $transactionId
     * @return bool
     */
    public function updateSuccessPayment(Order $order, ?string $transactionId = null): bool;

    /**
     * @param Order       $order
     * @param string|null $transactionId
     * @return bool
     */
    public function updatePendingPayment(Order $order, ?string $transactionId = null): bool;

    /**
     * @param Invoice $invoice
     * @return void
     */
    public function cancelOutdatedInvoices(Invoice $invoice): void;

    /**
     * @param Invoice $invoice
     * @param int     $gatewayId
     * @param array   $extra
     *
     * @return array|null
     */
    public function payment(Invoice $invoice, int $gatewayId, array $extra = []): ?array;

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
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewInvoicesAdminCP(User $context, array $attributes = []): Paginator;

    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewInvoices(User $context, array $attributes = []): Paginator;

    /**
     * @param User    $context
     * @param Invoice $invoice
     * @return bool
     */
    public function cancelInvoice(User $context, Invoice $invoice): bool;

    /**
     * @param User    $context
     * @param Invoice $invoice
     * @return bool
     */
    public function markAsPaid(User $context, Invoice $invoice): bool;

    /**
     * @param User $user
     * @param Item $item
     * @return Invoice
     */
    public function getUnpaidInvoiceForItem(User $user, Item $item): Invoice;
}
