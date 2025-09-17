<?php

namespace MetaFox\Advertise\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Invoice.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface InvoiceRepositoryInterface
{
    /**
     * @param User   $context
     * @param Entity $entity
     * @return Invoice
     */
    public function createInvoiceAdminCP(User $context, Entity $entity): Invoice;

    /**
     * @param User   $context
     * @param Entity $entity
     * @param array  $attributes
     * @return array
     */
    public function createInvoice(User $context, Entity $entity, array $attributes): array;

    /**
     * @param User   $context
     * @param int    $itemId
     * @param string $itemType
     * @return array|null
     */
    public function getRefreshInfo(User $context, int $itemId, string $itemType): ?array;

    /**
     * @param User     $context
     * @param int      $itemId
     * @param string   $itemType
     * @param int      $gatewayId
     * @param int|null $id
     * @param array    $extra
     *
     * @return array
     */
    public function paymentInvoice(User $context, int $itemId, string $itemType, int $gatewayId, ?int $id = null, array $extra = []): array;

    /**
     * @param int         $id
     * @param string|null $transactionId
     * @return void
     */
    public function updateSuccessPayment(int $id, ?string $transactionId): void;

    /**
     * @param int         $id
     * @param string|null $transactionId
     * @return void
     */
    public function updatePendingPayment(int $id, ?string $transactionId): void;

    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewInvoices(User $context, array $attributes = []): Paginator;

    /**
     * @param User $context
     * @param int  $id
     * @return Invoice
     */
    public function cancelInvoice(User $context, int $id): Invoice;

    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     */
    public function viewInvoicesAdminCP(User $context, array $attributes = []): Paginator;

    /**
     * @param User $context
     * @param int  $id
     * @return bool
     */
    public function deleteInvoice(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $invoiceId
     * @return Collection
     */
    public function viewTransactionsInAdminCP(User $context, int $invoiceId): Collection;

    /**
     * @param User $context
     * @param int  $id
     * @return Invoice
     */
    public function viewInvoice(User $context, int $id): Invoice;
}
