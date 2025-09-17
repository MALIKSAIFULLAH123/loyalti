<?php

namespace MetaFox\InAppPurchase\Listeners;

use MetaFox\InAppPurchase\Http\Resources\v1\Product\ProductDetail;
use MetaFox\InAppPurchase\Models\Product;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Class CanCancelSubscriptionListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CanCancelSubscriptionListener
{
    public function handle(?Entity $resource): ?bool
    {
        if (!$resource instanceof Entity) {
            return null;
        }
        $gateway = $resource->gateway;

        if ($gateway instanceof Gateway && $gateway->service === 'in-app-purchase') {
            return false;
        }

        return null;
    }
}
