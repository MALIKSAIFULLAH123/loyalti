<?php

namespace MetaFox\Subscription\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Subscription\Contracts\SubscriptionInvoiceContract;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Models\SubscriptionPackage;

/**
 * @method static getCancelUrl(string $location, ?Model $invoice = null)
 * @method static checkSubscriptionPackage(SubscriptionPackage $resource)
 * @method static getPendingInvoiceInRegistration(\MetaFox\Platform\Contracts\User $user)
 */
class SubscriptionInvoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SubscriptionInvoiceContract::class;
    }
}
