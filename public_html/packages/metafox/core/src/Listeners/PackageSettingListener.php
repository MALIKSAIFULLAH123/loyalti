<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Core\Jobs\CleanUpSiteStatistic;
use MetaFox\Core\Jobs\UpdateSiteStatistic;
use MetaFox\Core\Models\Link;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Core\Notifications\NewPostLinkToFollowerNotification;
use MetaFox\Core\Policies\Handlers\CanApprove;
use MetaFox\Core\Policies\Handlers\CanFeature;
use MetaFox\Core\Policies\Handlers\CanPublish;
use MetaFox\Core\Policies\Handlers\CanViewApprove;
use MetaFox\Core\Policies\Handlers\CanViewApproveListing;
use MetaFox\Core\Policies\LinkPolicy;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Models\Currency;
use MetaFox\Localize\Policies\CountryChildPolicy;
use MetaFox\Localize\Policies\CountryPolicy;
use MetaFox\Localize\Policies\CurrencyPolicy;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'models.notify.creating'               => [ModelCreatingListener::class],
            'models.notify.created'                => [ModelCreatedListener::class],
            'models.notify.updating'               => [ModelUpdatingListener::class],
            'models.notify.updated'                => [ModelUpdatedListener::class],
            'models.notify.deleted'                => [ModelDeletedListener::class],
            'activity.feed.deleted'                => [
                FeedDeletedListener::class,
            ],
            'core.get_privacy_id'                  => [
                GetPrivacyIdListener::class,
            ],
            'core.user_privacy.get_privacy_id'     => [
                GetPrivacyIdForUserPrivacyListener::class,
            ],
            'core.privacy.check_privacy_member'    => [
                CheckPrivacyMember::class,
            ],
            'feed.composer'                        => [
                FeedComposerListener::class,
            ],
            'feed.composer.edit'                   => [
                FeedComposerEditListener::class,
            ],
            'core.check_privacy_list'              => [
                CheckPrivacyListListener::class,
            ],
            'packages.scan'                        => [
                PackageScanListener::class,
            ],
            'packages.installed'                   => [
                PackageInstalledListener::class,
            ],
            'packages.deleted'                     => [
                PackageDeletedListener::class,
            ],
            'core.parse_url'                       => [
                ParseUrlListener::class,
            ],
            'core.process_parse_url'               => [
                ParseFacebookUrlListener::class,
                ParseTwitterUrlListener::class,
                ParseVimeoUrlListener::class,
                ParseInstagramUrlListener::class,
                ParseYouTubeUrlListener::class,
                ParseTiktokUrlListener::class,
                ParseRumbleUrlListener::class,
                ParseInternalUrlListener::class,
            ],
            'core.after_parse_url'                 => [
                ParseGenericUrlListener::class,
            ],
            'core.attachment.copy'                 => [
                CopyAttachmentListener::class,
            ],
            'activity.update_feed_item_privacy'    => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'core.privacy_stream.create'           => [
                CreatePrivacyStreamListener::class,
            ],
            'user.deleted'                         => [
                UserDeletedListener::class,
            ],
            'core.privacy.get_default'             => [
                GetDefaultPrivacyDetail::class,
            ],
            'core.setting.get_eloquent_builder'    => [
                GetSettingEloquentBuilderListener::class,
            ],
            'parseRoute'                           => [
                ParseRouteListener::class,
                ParseRedirectListener::class,
            ],
            'core.driver.get_package_id_by_entity' => [
                GetPackageIdByEntityListener::class,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        $settings = app('files')->getRequire(base_path('packages/metafox/core/resources/settings.php'));

        return array_merge($settings, [
            'data_item_map.link'                    => [
                'config_name' => 'notification.data_item_map.link',
                'module_id'   => 'notification',
                'is_public'   => 0,
                'value'       => 'feed',
            ],
            'security.header_content_type_options'  => [
                'config_name' => 'security.header_content_type_options',
                'is_public'   => 0,
                'value'       => 'nosniff',
            ],
            'security.header_access_control_origin' => [
                'config_name' => 'security.header_access_control_origin',
                'is_public'   => 0,
                'value'       => '*',
            ],
            'homepage_login_required'               => [
                'config_name' => 'homepage_login_required',
                'is_public'   => 1,
                'value'       => true,
            ],
        ]);
    }

    public function getUserPrivacy(): array
    {
        return [
            'core.view_browse_widgets' => [
                'phrase'  => 'core::phrase.user_privacy.can_view_widgets',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'core.view_admins'         => [
                'phrase'  => 'core::phrase.user_privacy.who_can_view_admins',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'core.view_publish_date'   => [
                'phrase'  => 'core::phrase.user_privacy.who_can_view_publish_date',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'core.view_members'        => [
                'phrase'  => 'core::phrase.user_privacy.who_can_view_members',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Link::ENTITY_TYPE => [
                'report' => UserRole::LEVEL_REGISTERED,
            ],
            'admincp'         => [
                'has_admin_access'         => [
                    'roles'       => UserRole::LEVEL_STAFF,
                    'is_editable' => 0,
                ],
                'can_add_new_block'        => UserRole::LEVEL_ADMINISTRATOR,
                'can_view_product_options' => UserRole::LEVEL_ADMINISTRATOR,
                'can_clear_site_cache'     => UserRole::LEVEL_ADMINISTRATOR,
                'has_system_access'        => [
                    'type'          => MetaFoxDataType::BOOLEAN,
                    'default'       => 0,
                    'roles'         => UserRole::LEVEL_ADMINISTRATOR,
                    'require_admin' => 1,
                ],
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            'attachment' => [],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Country::class      => CountryPolicy::class,
            CountryChild::class => CountryChildPolicy::class,
            Currency::class     => CurrencyPolicy::class,
            Link::class         => LinkPolicy::class,
        ];
    }

    public function getPolicyHandlers(): array
    {
        return [
            'feature'            => CanFeature::class,
            'approve'            => CanApprove::class,
            'viewApprove'        => CanViewApprove::class,
            'publish'            => CanPublish::class,
            'viewApproveListing' => CanViewApproveListing::class,
        ];
    }

    public function getActivityTypes(): array
    {
        return [
            [
                'type'                         => Link::ENTITY_TYPE,
                'entity_type'                  => Link::ENTITY_TYPE,
                'is_active'                    => true,
                'title'                        => 'core::phrase.link_type',
                'description'                  => 'user_posted_a_post_on_timeline',
                'is_system'                    => 0,
                'can_comment'                  => true,
                'can_like'                     => true,
                'can_share'                    => true,
                'can_edit'                     => true,
                'can_create_feed'              => true,
                'can_change_privacy_from_feed' => true,
                'prevent_delete_feed_items'    => true,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'              => 'post_link_follower_notification',
                'module_id'         => 'core',
                'require_module_id' => 'follow',
                'title'             => 'core::phrase.post_link_follower_notification_type',
                'handler'           => NewPostLinkToFollowerNotification::class,
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'          => 8,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(new UpdateSiteStatistic())
            ->hourly()
            ->withoutOverlapping();

        $schedule->job(new UpdateSiteStatistic(StatsContent::STAT_PERIOD_ONE_HOUR))
            ->hourly()
            ->withoutOverlapping();

        $schedule->job(new CleanUpSiteStatistic(StatsContent::STAT_PERIOD_ONE_HOUR))
            ->monthly()
            ->withoutOverlapping();
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('core::phrase.links'),
                'value' => 'link',
            ],
        ];
    }
}
