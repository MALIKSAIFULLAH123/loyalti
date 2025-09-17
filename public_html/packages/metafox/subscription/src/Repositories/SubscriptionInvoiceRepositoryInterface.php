<?php

namespace MetaFox\Subscription\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Models\SubscriptionInvoiceTransaction;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SubscriptionInvoice.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SubscriptionInvoiceRepositoryInterface
{
    /**
     * @param  User                     $context
     * @return SubscriptionInvoice|null
     */
    public function getUserActiveSubscription(User $context): ?SubscriptionInvoice;

    /**
     * @param User  $context
     * @param array $attributes
     * @param array $extra
     *
     * @return array
     */
    public function createInvoice(User $context, array $attributes, array $extra = []): array;

    /**
     * @param  int    $id
     * @param  string $status
     * @param  array  $extra
     * @return bool
     */
    public function updatePayment(int $id, string $status, array $extra = []): bool;

    /**
     * @param  User                                $context
     * @param  int                                 $invoiceId
     * @param  string                              $paymentStatus
     * @param  float|null                          $price
     * @param  string|null                         $transactionId
     * @return SubscriptionInvoiceTransaction|null
     */
    public function createTransaction(
        User $context,
        int $invoiceId,
        string $paymentStatus,
        ?float $price = null,
        ?string $transactionId = null,
        ?string $currency = null
    ): ?SubscriptionInvoiceTransaction;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewInvoices(User $context, array $attributes): Paginator;

    /**
     * @param  User                $context
     * @param  int                 $id
     * @return SubscriptionInvoice
     */
    public function viewInvoice(User $context, int $id): SubscriptionInvoice;

    /**
     * @param  int   $invoiceId
     * @param  array $attributes
     * @return array
     */
    public function viewTransactions(int $invoiceId, array $attributes): array;

    /**
     * @param  User  $context
     * @param  int   $id
     * @param  array $attributes
     * @return bool
     */
    public function cancelSubscriptionByUser(User $context, int $id, array $attributes = []): bool;

    /**
     * @param User  $context
     * @param int   $id
     * @param array $attributes
     * @param array $extra
     *
     * @return array
     */
    public function renewInvoice(User $context, int $id, array $attributes, array $extra = []): array;

    /**
     * @param  int                      $id
     * @return SubscriptionInvoice|null
     */
    public function changeInvoice(User $context, int $id): ?SubscriptionInvoice;

    /**
     * @param User  $context
     * @param int   $id
     * @param array $attributes
     * @param array $extra
     *
     * @return array|null
     */
    public function upgrade(User $context, int $id, array $attributes, array $extra = []): ?array;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewInvoicesInAdminCP(User $context, array $attributes = []): Paginator;

    /**
     * @param  User   $context
     * @param  int    $id
     * @param  string $status
     * @return bool
     */
    public function updatePaymentForAdminCP(User $context, int $id, string $status): bool;

    /**
     * @param  int        $packageId
     * @param  bool       $canceledByGateway
     * @return Collection
     */
    public function getExpiredSubscriptions(int $packageId, bool $canceledByGateway = false): Collection;

    /**
     * @param  int        $packageId
     * @return Collection
     */
    public function getNotifiedInvoices(int $packageId): Collection;

    /**
     * @param  int        $packageId
     * @return Collection
     */
    public function getCanceledSubscriptionsByGateway(int $packageId): Collection;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function handleRegistration(User $context, int $id): bool;

    /**
     * @param  User $context
     * @return bool
     */
    public function hasPaidInvoices(User $context): bool;

    /**
     * @param  array  $packageIds
     * @param  string $status
     * @param  string $fromDate
     * @param  string $toDate
     * @return array
     */
    public function getStatisticsByPaymentStatus(
        array $packageIds,
        string $status,
        string $fromDate,
        string $toDate
    ): array;

    /**
     * @param  User                $context
     * @param  SubscriptionInvoice $invoice
     * @return bool
     */
    public function cancelSubscriptionByDowngrade(User $context, SubscriptionInvoice $invoice): bool;

    /**
     * @param  int  $id
     * @return bool
     */
    public function hasCompletedTransactions(int $id): bool;

    /**
     * @param  SubscriptionInvoice $invoice
     * @param  float|null          $total
     * @param  string|null         $transactionId
     * @return mixed
     */
    public function recycleInvoice(SubscriptionInvoice $invoice, ?float $total = null, ?string $transactionId = null);

    /**
     * @param  User $user
     * @return bool
     */
    public function cancelAllUserActiveSubscriptions(User $user): bool;
}
