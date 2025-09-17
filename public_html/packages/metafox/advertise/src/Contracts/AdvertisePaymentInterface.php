<?php

namespace MetaFox\Advertise\Contracts;

use MetaFox\Advertise\Models\Invoice;
use MetaFox\Platform\Contracts\User;

/**
 * @property        string           $payment_order_title
 */
interface AdvertisePaymentInterface
{
    /**
     * @param  Invoice $invoice
     * @return bool
     */
    public function isPriceChanged(Invoice $invoice): bool;

    /**
     * @param  User $user
     * @return bool
     */
    public function isFree(User $user): bool;

    /**
     * @param  User  $user
     * @return array
     */
    public function toPayment(User $user): array;

    /**
     * @param  Invoice $invoice
     * @return bool
     */
    public function toCompletedPayment(Invoice $invoice): bool;

    /**
     * @param  Invoice $invoice
     * @return bool
     */
    public function toPendingItem(Invoice $invoice): void;

    /**
     * @param  float  $price
     * @param  string $currencyId
     * @return string
     */
    public function getChangePriceMessage(float $price, string $currencyId): string;

    /**
     * @return string
     */
    public function getFreePriceMessage(): string;

    /**
     * @return string
     */
    public function toPaymentDescription(): string;
}
