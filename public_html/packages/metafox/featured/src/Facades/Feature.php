<?php

namespace MetaFox\Featured\Facades;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Facade;
use MetaFox\Featured\Contracts\SupportInterface;
use MetaFox\Featured\Models\Item;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * @method static array                getDurationOptions()
 * @method static array                getCurrencyOptions(?string $userCurrency = null)
 * @method static array                getApplicableItemTypeOptions()
 * @method static array                getApplicableRoleOptions()
 * @method static array                getDurationOptionsForSearch()
 * @method static array                getPricingOptions()
 * @method static array                getStatusOptions()
 * @method static string               getDurationText(?string $durationPeriod, ?int $durationValue)
 * @method static string               getEntityTypeLabel(string $moduleId, string $entityType)
 * @method static array                getAllowedEntityTypes()
 * @method static bool                 isAllowedEntityType(string $entityType)
 * @method static Content              morphItemFromEntityType(string $entityType, int $entityId)
 * @method static string               getCompletedPaymentStatus()
 * @method static string               getPendingPaymentStatus()
 * @method static string               getAllPaymentStatus()
 * @method static string               getInitPaymentStatus()
 * @method static string               getCancelledPaymentStatus()
 * @method static void                 activateItemFeatured(?Content $item)
 * @method static void                 deactivateItemFeatured(?Content $item)
 * @method static void                 increasePackageTotalActive(int $packageId)
 * @method static void                 increasePackageTotalEnd(int $packageId)
 * @method static void                 increasePackageTotalCancelled(int $packageId)
 * @method static void                 decreasePackageTotalActive(int $packageId)
 * @method static void                 decreasePackageTotalEnd(int $packageId)
 * @method static void                 decreasePackageTotalCancelled(int $packageId)
 * @method static bool                 hasAvailablePackages(User $user, string $entityType)
 * @method static CarbonInterface|null getExpiredDatetimeByDuration(?string $durationPeriod, ?int $durationValue)
 * @method static void                 markFeaturedItemEnded(Item $item)
 * @method static void                 handleContentDeleted(Content $content)
 * @method static void                 handleItemDeleted(Item $item)
 * @method static bool                 isContentAvailableForFeature(Content $content)
 * @method static bool                 isFeaturedByUser(User $user, Content $content)
 * @method static array                getSearchInvoiceStatusOptions()
 * @method static string|null          getInvoiceStatusText(string $status)
 * @method static array                getSearchItemStatusOptions()
 * @method static string|null          getItemStatusText(string $status)
 * @method static array                getTransactionStatusSearchOptions()
 * @method static array                getGatewaySearchOptions()
 * @method static array                getSearchFormResponsiveSx()
 * @method static string|null          getPriceFormatted(float $price, string $currency)
 * @method static array                handleRequestEmptyStringValue(array $data, string $index)
 * @method static array                handleRequestEmptyIntegerValue(array $data, string $index)
 * @method static array                getPackageSearchOptions()
 * @method static string               getTransactionIdForFree()
 * @method static array                getInvoiceStatusColors()
 * @method static array|null           getInvoiceStatusInfo(string $status)
 * @method static string|null          getEntityTypeLabelByEntityType(string $entityType)
 * @method static string|null          getPricingLabel(string $option)
 * @method static bool                 isUsingMultiStepFormForEwallet()
 * @method static string|null          getItemTitle(?Content $content)
 * @method static array                getAllowedRoleOptions()
 * @method static array                getAllowedRole()
 */
class Feature extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SupportInterface::class;
    }
}
