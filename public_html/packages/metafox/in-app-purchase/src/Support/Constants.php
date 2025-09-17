<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Support;

class Constants
{
    public const APPLE_SERVER_API_URL         = 'https://api.storekit.itunes.apple.com/inApps/v1';
    public const APPLE_SANDBOX_SERVER_API_URL = 'https://api.storekit-sandbox.itunes.apple.com/inApps/v1';
    public const GOOGLE_ANDROID_PUBLISHER_URL = 'https://www.googleapis.com/androidpublisher/v3/applications';

    public const IOS = 'ios';

    public const ANDROID = 'android';

    public const APPLE_AUTO_RENEWABLE_SUBSCRIPTION = 'Auto-Renewable Subscription';
    public const APPLE_NON_CONSUMABLE              = 'Non-Consumable';
    public const APPLE_CONSUMABLE                  = 'Consumable';
    public const APPLE_NON_RENEWING_SUBSCRIPTION   = 'Non-Renewing Subscription';
}
