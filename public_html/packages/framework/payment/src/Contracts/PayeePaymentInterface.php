<?php

namespace MetaFox\Payment\Contracts;

use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\User;

interface PayeePaymentInterface
{
    /**
     * @param  Order $order
     * @param  User  $payee
     * @param  array $params
     * @return array
     */
    public function createPayeeOrder(Order $order, User $payee, array $params = []): array;
}
