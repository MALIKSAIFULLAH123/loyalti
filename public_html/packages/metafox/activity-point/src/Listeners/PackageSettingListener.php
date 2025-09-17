<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\ActivityPoint\Models\PointSetting;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\ActivityPoint\Notifications\AdjustPointsNotification;
use MetaFox\ActivityPoint\Notifications\ApprovedConversionRequestNotification;
use MetaFox\ActivityPoint\Notifications\DeniedConversionRequestNotification;
use MetaFox\ActivityPoint\Notifications\PendingConversionRequestNotification;
use MetaFox\ActivityPoint\Notifications\PurchasePackageFailedNotification;
use MetaFox\ActivityPoint\Notifications\PurchasePackageSuccessNotification;
use MetaFox\ActivityPoint\Notifications\ReceivedGiftedPointsNotification;
use MetaFox\ActivityPoint\Policies\PackagePolicy;
use MetaFox\ActivityPoint\Policies\PackagePurchasePolicy;
use MetaFox\ActivityPoint\Policies\PointSettingPolicy;
use MetaFox\ActivityPoint\Policies\StatisticPolicy;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\ActivityPoint\Support\Handlers\EditPermissionListener;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;

/**
 * Class PackageSettingListener.
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageSettingListener extends BasePackageSettingListener
{
    /**
     * @return array<string, mixed>
     */
    public function getEvents(): array
    {
        return [
            'activitypoint.point_updated' => [
                PointUpdatedListener::class,
            ],
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'models.notify.deleting' => [
                ModelDeletingListener::class,
            ],
            'payment.payment_success' => [
                OrderSuccessProcessed::class,
            ],
            'activitypoint.increase_user_point' => [
                IncreaseUserPointListener::class,
            ],
            'activitypoint.decrease_user_point' => [
                DecreaseUserPointListener::class,
            ],
            'activitypoint.get_point_statistics' => [
                GetPointStatisticsListener::class,
            ],
            'user.permissions.extra' => [
                UserExtraPermissionListener::class,
            ],
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'packages.activated' => [
                PackageActivatedListener::class,
            ],
            'user.role.created' => [
                UserRoleCreatedListener::class,
            ],
            'importer.completed' => [
                ImporterCompleted::class,
            ],
            'activitypoint.decrease_user_point.custom' => [
                DecreaseUserPointWithCustomActionListener::class,
            ],
            'payment.place_order_processed' => [
                PlaceOrderProcessedListener::class,
            ],
            'payment.migrate_payment_gateway_id' => [
                MigratePaymentGatewayId::class,
            ],
            'user.deleted' => [
                UserDeletedListener::class,
                DecreaseUserPointWithCustomActionListener::class,
            ],
            // Temporary disable In-app on Activity Point
            //            'in-app-purchase.get_product_type' => [
            //                IapGetProductTypeListener::class,
            //            ],
            //            'in-app-purchase.create_invoice' => [
            //                IapCreateInvoiceListener::class,
            //            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'activitypoint' => [
                'can_purchase_with_activity_points' => UserRole::LEVEL_REGISTERED,
                'can_purchase_points'               => UserRole::LEVEL_REGISTERED,
                'can_gift_activity_points'          => UserRole::LEVEL_REGISTERED,
                'can_adjust_activity_points'        => [
                    'roles'     => UserRole::LEVEL_STAFF,
                    'is_public' => 0,
                ],
            ],
            ConversionRequest::ENTITY_TYPE => [
                'auto_approved' => UserRole::LEVEL_ADMINISTRATOR,
                'create'        => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            'activitypoint' => [
                'maximum_activity_points_admin_can_adjust' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 1,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 1000,
                        UserRole::STAFF_USER  => 50,
                        UserRole::NORMAL_USER => 0,
                    ],
                    'extra' => [
                        'fieldCreator' => [EditPermissionListener::class, 'maximumActivityPointsAdminCanAdjust'],
                    ],
                ],
                'period_time_admin_adjust_activity_points' => [
                    'description' => 'period_time_admin_adjust_activity_points',
                    'type'        => MetaFoxDataType::INTEGER,
                    'default'     => 1,
                    'roles'       => [
                        UserRole::ADMIN_USER  => 1,
                        UserRole::STAFF_USER  => 1,
                        UserRole::NORMAL_USER => 1,
                    ],
                    'extra' => [
                        'fieldCreator' => [EditPermissionListener::class, 'periodTimeAdminAdjustActivityPoints'],
                    ],
                ],
            ],
            ConversionRequest::ENTITY_TYPE => [
                'max_points_per_day' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                ],
                'max_points_per_month' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                ],
                'min_points_for_conversion' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'minPointsForConversion'],
                    ],
                ],
                'max_points_for_conversion' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'maxPointsForConversion'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getUserValues(): array
    {
        return [
            User::ENTITY_TYPE => [
                ActivityPoint::TOTAL_POINT_VALUE_NAME => [
                    'default_value' => 0,
                    'ordering'      => 1,
                ],
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            PointPackage::class    => PackagePolicy::class,
            PointStatistic::class  => StatisticPolicy::class,
            PointSetting::class    => PointSettingPolicy::class,
            PackagePurchase::class => PackagePurchasePolicy::class,
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'conversion_rate' => [
                'value' => [
                    'USD' => 1,
                    'EUR' => 1,
                    'GBP' => 1,
                ],
            ],
            'conversion_request_fee' => [
                'type'  => 'float',
                'value' => 0,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'purchase_package_success',
                'module_id'  => 'activitypoint',
                'handler'    => PurchasePackageSuccessNotification::class,
                'title'      => 'activitypoint::phrase.purchase_package_success',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'purchase_package_fail',
                'module_id'  => 'activitypoint',
                'handler'    => PurchasePackageFailedNotification::class,
                'title'      => 'activitypoint::phrase.purchase_package_fail_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 2,
            ],
            [
                'type'       => 'received_gifted_points',
                'module_id'  => 'activitypoint',
                'handler'    => ReceivedGiftedPointsNotification::class,
                'title'      => 'activitypoint::phrase.received_gifted_points',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 3,
            ],
            [
                'type'       => 'adjust_points',
                'module_id'  => 'activitypoint',
                'handler'    => AdjustPointsNotification::class,
                'title'      => 'activitypoint::phrase.adjust_points',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 4,
            ],

            [
                'type'       => 'activitypoint_pending_conversion_request_notification',
                'module_id'  => 'activitypoint',
                'handler'    => PendingConversionRequestNotification::class,
                'title'      => 'activitypoint::phrase.pending_conversion_request_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'activitypoint_denied_conversion_request_notification',
                'module_id'  => 'activitypoint',
                'handler'    => DeniedConversionRequestNotification::class,
                'title'      => 'activitypoint::phrase.denied_conversion_request_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'activitypoint_approved_conversion_request_notification',
                'module_id'  => 'activitypoint',
                'handler'    => ApprovedConversionRequestNotification::class,
                'title'      => 'activitypoint::phrase.approved_conversion_request_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
        ];
    }
}
