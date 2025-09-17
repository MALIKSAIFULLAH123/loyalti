<?php

namespace MetaFox\Subscription\Contracts;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Models\SubscriptionPendingRegistrationUser;

interface SubscriptionInvoiceContract
{
    /**
     * @param  int     $id
     * @param  string  $status
     * @param  array   $extra
     * @return bool
     */
    public function updatePayment(int $id, string $status, array $extra = []): bool;

    /**
     * @param  int    $id
     * @param  array  $attributes
     * @return array
     */
    public function getTransactions(int $id, array $attributes = []): array;

    /**
     * @return array
     */
    public function getTableFields(): array;

    /**
     * @param  User   $context
     * @param  Model  $invoice
     * @return array
     */
    public function getPaymentButtons(User $context, Model $invoice): array;

    /**
     * @param  Model  $invoice
     * @return string
     */
    public function getReturnUrl(Model $invoice): string;

    /**
     * @param  string      $location
     * @param  Model|null  $invoice
     * @return string
     */
    public function getCancelUrl(string $location, ?Model $invoice = null): string;

    /**
     * @param  User  $context
     * @param  int   $id
     * @return bool
     */
    public function handleRegistration(User $context, int $id): bool;

    /**
     * @param  User  $user
     * @return SubscriptionPendingRegistrationUser|null
     */
    public function getPendingInvoiceInRegistration(User $user): ?SubscriptionPendingRegistrationUser;

    /**
     * @param  User  $context
     * @return bool
     */
    public function hasPaidInvoices(User $context): bool;

    /**
     * @param  SubscriptionPackage  $resource
     * @return bool
     */
    public function checkSubscriptionPackage(SubscriptionPackage $resource): bool;
}
