<?php

namespace MetaFox\Marketplace\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Marketplace\Contracts\ListingSupportContract;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Platform\Contracts\User;

/**
 * @method static array       getPaymentStatus()
 * @method static string      getAllPaymentStatus()
 * @method static string      getCompletedPaymentStatus()
 * @method static string      getPendingPaymentStatus()
 * @method static string      getInitPaymentStatus()
 * @method static string      getCanceledPaymentStatus()
 * @method static int         getMaximumTitleLength()
 * @method static int         getMinimumTitleLength()
 * @method static array       getInviteMethodTypes()
 * @method static string      getActiveStatus ()
 * @method static string      getInactiveStatus ()
 * @method static array       getInviteLinkStatus ()
 * @method static string|null getUserPriceFormat(User $user, array $prices)
 * @method static float|null  getUserPrice(User $user, array $prices)
 * @method static array|null  getUserPaymentInformation(User $user, array $prices)
 * @method static array|null  getPaymentGatewayParams(User $user, Model $resource)
 * @method static string      getInviteUserType()
 * @method static string|null getStatusLabel(string $status)
 * @method static array       getInvoiceStatusOptionForFrom()
 * @method static array       getListingForForm(User $context, array $attributes = [])
 * @method static string|null getPriceFormat(string $currency, float $price)
 * @method static float|null  getPriceByCurrency(string $currency, array $price)
 * @method static bool        isExpired(?Model $listing)
 * @method static bool        isFree(User $user, array $prices)
 * @method static string|null getExpiredLabel(Model $listing, bool $isListing = true)
 * @method static bool        isActivityPointAppActive()
 * @method static bool        enableTopic(User $user, ?User $owner)
 * @method static array       getFormValues(User $context, \MetaFox\Marketplace\Models\Listing $listing)
 * @method static array       getStatusInfo(string $status)
 * @method static array       getStatusTexts(\MetaFox\Marketplace\Models\Listing $listing)
 * @method static bool        isUsingMultiStepFormForEwallet()
 */
class Listing extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ListingSupportContract::class;
    }
}
