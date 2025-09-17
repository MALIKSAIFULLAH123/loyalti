<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Blog\Listeners;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Notifications\BlogApproveNotification;
use MetaFox\Blog\Notifications\NewBlogToFollowerNotification;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Blog\Policies\CategoryPolicy;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    /**
     * @return string[]
     */
    public function getCaptchaRules(): array
    {
        return ['create_blog'];
    }

    public function getActivityTypes(): array
    {
        return [
            [
                'type'                   => Blog::ENTITY_TYPE,
                'entity_type'            => Blog::ENTITY_TYPE,
                'is_active'              => true,
                'title'                  => 'blog::phrase.blog_type',
                'description'            => 'added_a_blog',
                'is_system'              => 0,
                'can_comment'            => true,
                'can_like'               => true,
                'can_share'              => true,
                'can_edit'               => false,
                'can_create_feed'        => true,
                'can_redirect_to_detail' => true,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'blog_approve_notification',
                'module_id'  => 'blog',
                'handler'    => BlogApproveNotification::class,
                'title'      => 'blog::phrase.blog_approve_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 18,
            ],
            [
                'type'              => 'blog_follower_notification',
                'module_id'         => 'blog',
                'require_module_id' => 'follow',
                'handler'           => NewBlogToFollowerNotification::class,
                'title'             => 'blog::phrase.blog_follower_notification_type',
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'          => 18,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Blog::class     => BlogPolicy::class,
            Category::class => CategoryPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Blog::ENTITY_TYPE => [
                'view'                        => UserRole::LEVEL_GUEST,
                'create'                      => UserRole::LEVEL_REGISTERED,
                'update'                      => UserRole::LEVEL_REGISTERED,
                'delete'                      => UserRole::LEVEL_REGISTERED,
                'moderate'                    => UserRole::LEVEL_ADMINISTRATOR,
                'feature'                     => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'purchase_feature'            => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'approve'                     => UserRole::LEVEL_STAFF,
                'save'                        => UserRole::LEVEL_REGISTERED,
                'like'                        => UserRole::LEVEL_REGISTERED,
                'share'                       => UserRole::LEVEL_REGISTERED,
                'comment'                     => UserRole::LEVEL_REGISTERED,
                'report'                      => UserRole::LEVEL_REGISTERED,
                'sponsor'                     => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'sponsor_free'                => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'sponsor_in_feed'             => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'auto_publish_sponsored_item' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'auto_approved'               => UserRole::LEVEL_REGISTERED,
                'download_attachment'         => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'minimum_name_length'         => ['value' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH],
            'maximum_name_length'         => ['value' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'default_category'            => ['value' => 1],
            'blog.purchase_sponsor_price' => [
                'value'     => '',
                'is_public' => false,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'models.notify.updated'                    => [
                ModelUpdatedListener::class,
            ],
            'activity.update_feed_item_privacy'        => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'like.notification_to_callback_message'    => [
                LikeNotificationMessageListener::class,
            ],
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'core.collect_total_items_stat'            => [
                CollectTotalItemsStatListener::class,
            ],
            'user.deleted'                             => [
                UserDeletedListener::class,
            ],
            'advertise.sponsor.enable_sponsor_feed'    => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed'   => [
                DisableSponsorFeedListener::class,
            ],
            'importer.completed'                       => [
                ImporterCompleted::class,
            ],
            'activity.feed.deleted'                    => [
                FeedDeletedListener::class,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'blog.share_blogs'       => [
                'phrase' => 'blog::phrase.user_privacy.who_can_share_blogs',
            ],
            'blog.view_browse_blogs' => [
                'phrase' => 'blog::phrase.user_privacy.who_can_view_blogs',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page'  => [
                'blog.share_blogs',
                'blog.view_browse_blogs',
            ],
            'group' => [
                'blog.share_blogs',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [
            Blog::ENTITY_TYPE => [
                'phrase'  => 'blog::phrase.blogs',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getProfileMenu(): array
    {
        return [
            Blog::ENTITY_TYPE => [
                'phrase'  => 'blog::phrase.blogs',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Blog::ENTITY_TYPE => [
                'flood_control'                                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control'                                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control_' . MetaFoxConstant::TIMEFRAME_DAILY   => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'action'  => 'quota_control',
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control_' . MetaFoxConstant::TIMEFRAME_MONTHLY => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'action'  => 'quota_control',
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
            ],
        ];
    }

    public function getItemTypes(): array
    {
        return [
            Blog::ENTITY_TYPE,
        ];
    }

    /**
     * @return string[]|null
     */
    public function getSiteStatContent(): ?array
    {
        return [
            Blog::ENTITY_TYPE => ['icon' => 'ico-newspaper-alt'],
            'pending_blog'    => [
                'icon' => 'ico-clock-o',
                'to'   => '/blog/blog/browse?view=' . Browse::VIEW_PENDING,
            ],
        ];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('blog::phrase.blogs'),
                'value' => 'blog',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return [
            'blog',
            'blog_category',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/blog',
                'name' => 'blog::phrase.ad_mob_home_page',
            ],
            [
                'path' => '/blog/:id',
                'name' => 'blog::phrase.ad_mob_detail_page',
            ],
        ];
    }
}
