<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Contracts;

use MetaFox\Payment\Models\Order;

/**
 * Interface HasSupportSubscription.
 * payment gateway interface.
 */
interface HasSupportSubscription
{
    /**
     * createGatewaySubscription.
     *
     * @param  Order                $order
     * @param  array<string, mixed> $params additional parameters
     * @return array<string, mixed>
     */
    public function createGatewaySubscription(Order $order, array $params = []): array;

    /**
     * cancelGatewaySubscription.
     *
     * @param  Order                $order
     * @return array<string, mixed>
     */
    public function cancelGatewaySubscription(Order $order): array;

    /**
     * getGatewaySubscription.
     *
     * @param  string                $gatewaySubscriptionId
     * @return ?array<string, mixed>
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getGatewaySubscription(string $gatewaySubscriptionId): ?array;
}
