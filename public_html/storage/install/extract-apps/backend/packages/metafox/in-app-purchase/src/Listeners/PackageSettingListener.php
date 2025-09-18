<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getSiteSettings(): array
    {
        return [
            'enable_iap_ios' => [
                'value'       => true,
                'config_name' => 'in-app-purchase.enable_iap_ios',
                'env_var'     => 'MFOX_IAP_ENABLE_IOS',
            ],
            'enable_iap_android' => [
                'value'       => true,
                'config_name' => 'in-app-purchase.enable_iap_android',
                'env_var'     => 'MFOX_IAP_ENABLE_ANDROID',
            ],
            'enable_iap_sandbox_mode' => [
                'value'       => false,
                'config_name' => 'in-app-purchase.enable_iap_sandbox_mode',
                'env_var'     => 'MFOX_IAP_ENABLE_SANDBOX_MODE',
            ],
            'apple_app_id' => [
                'value'       => '',
                'is_public'   => false,
                'config_name' => 'in-app-purchase.apple_app_id',
                'env_var'     => 'MFOX_IAP_APPLE_APP_ID',
            ],
            'apple_issuer_id' => [
                'value'       => '',
                'is_public'   => false,
                'config_name' => 'in-app-purchase.apple_issuer_id',
                'env_var'     => 'MFOX_IAP_APPLE_ISSUER_ID',
            ],
            'apple_key_id' => [
                'value'       => '',
                'is_public'   => false,
                'config_name' => 'in-app-purchase.apple_key_id',
                'env_var'     => 'MFOX_IAP_APPLE_KEY_ID',
            ],
            'apple_bundle_id' => [
                'value'       => '',
                'is_public'   => false,
                'config_name' => 'in-app-purchase.apple_bundle_id',
                'env_var'     => 'MFOX_IAP_APPLE_BUNDLE_ID',
            ],
            'apple_private_key' => [
                'value'       => '',
                'is_public'   => false,
                'config_name' => 'in-app-purchase.apple_private_key',
                'env_var'     => 'MFOX_IAP_APPLE_PRIVATE_KEY',
            ],
            'google_android_package_name' => [
                'value'       => '',
                'is_public'   => false,
                'config_name' => 'in-app-purchase.google_android_package_name',
                'env_var'     => 'MFOX_IAP_GOOGLE_ANDROID_PACKAGE_NAME',
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
            'resource.get_iap_product' => [
                GetProductListener::class,
            ],
            'subscription.can_cancel_subscription' => [
                CanCancelSubscriptionListener::class,
            ],
            'subscription.can_make_payment' => [
                CanPaySubscriptionListener::class,
            ],
            'subscription.can_upgrade_subscription' => [
                CanUpgradeSubscriptionListener::class,
            ],
            'user.pending_subscription.allow_endpoints' => [
                PendingSubscriptionAllowEndPointListener::class,
            ],
        ];
    }
}
