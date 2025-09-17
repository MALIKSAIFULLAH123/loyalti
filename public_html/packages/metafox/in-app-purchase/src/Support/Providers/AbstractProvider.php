<?php

namespace MetaFox\InAppPurchase\Support\Providers;

use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;

abstract class AbstractProvider
{
    protected ?Gateway $gateway;

    public function __construct()
    {
        $this->gateway = Payment::getManager()->getGatewayByName('in-app-purchase');
    }

    public function getOrderRepository(): OrderRepositoryInterface
    {
        return resolve(OrderRepositoryInterface::class);
    }

    public function getProductRepository(): ProductRepositoryInterface
    {
        return resolve(ProductRepositoryInterface::class);
    }

    public function handleCallback(array $data): bool
    {
        return false;
    }
}
