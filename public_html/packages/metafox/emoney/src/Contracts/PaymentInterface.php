<?php

namespace MetaFox\EMoney\Contracts;

use MetaFox\Payment\Models\Order;

interface PaymentInterface
{
    /**
     * @param Order $order
     * @param array $extra
     *
     * @return array|null
     */
    public function processPayment(Order $order, array $extra = []): ?array;

    /**
     * @param Order $order
     *
     * @return string
     */
    public function generateTransactionId(Order $order): string;

    /**
     * @param Order $order
     *
     * @return string
     */
    public function generateOrderId(Order $order): string;
}
