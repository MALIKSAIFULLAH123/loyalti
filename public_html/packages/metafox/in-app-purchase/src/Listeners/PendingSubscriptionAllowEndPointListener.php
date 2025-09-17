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
 * Class PendingSubscriptionAllowEndPointListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PendingSubscriptionAllowEndPointListener
{
    public function handle(): array
    {
        return [
            'in-app-purchase',
            'auth\/profile',
            'user\/refresh',
            'authorization\/(\w|\/|-)*',
        ];
    }
}
