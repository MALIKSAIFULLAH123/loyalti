<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Models\Follow;
use MetaFox\Follow\Policies\FollowPolicy;
use MetaFox\Platform\MetaFoxPrivacy;
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
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getPolicies(): array
    {
        return [
            Follow::class => FollowPolicy::class,
        ];
    }

    public function getPolicyHandlers(): array
    {
        return [];
    }

    public function getEvents(): array
    {
        return [
            'follow.is_follow'                             => [
                IsFollowListener::class,
            ],
            'follow.can_follow'                            => [
                CanFollowListener::class,
            ],
            'follow.add_follow'                            => [
                AddFollowListener::class,
            ],
            'user.permissions.extra'                       => [
                UserExtraPermissionListener::class,
            ],
            'user.blocked'                                 => [
                UserBlockedListener::class,
            ],
            'follow.get_total_follow'                      => [
                TotalFollowListener::class,
            ],
            'notification.new_post_to_follower'            => [
                SendNotificationToFollowerListener::class,
            ],
            'notification.delete_notification_to_follower' => [
                DeleteNotifyToFollowerListener::class,
            ],
            'models.notify.created'                        => [
                ModelCreatedListener::class,
            ],
            'models.notify.approved'                       => [
                ModelApprovedListener::class,
            ],
            'follow.get_builder_follower'                  => [
                GetBuilderFollowerListener::class,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'follow.add_follow'     => [
                'phrase' => 'follow::phrase.user_privacy.who_can_follow_me',
            ],
            'follow.view_following' => [
                'phrase' => 'follow::phrase.user_privacy.who_can_view_your_following',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'user' => [
                'follow.add_follow'     => [
                    'default' => MetaFoxPrivacy::EVERYONE,
                    'list'    => [
                        MetaFoxPrivacy::EVERYONE,
                        MetaFoxPrivacy::MEMBERS,
                        MetaFoxPrivacy::FRIENDS,
                        MetaFoxPrivacy::ONLY_ME,
                    ],
                ],
                'follow.view_following' => [
                    'default' => MetaFoxPrivacy::MEMBERS,
                    'list'    => [
                        MetaFoxPrivacy::EVERYONE,
                        MetaFoxPrivacy::MEMBERS,
                        MetaFoxPrivacy::FRIENDS,
                        MetaFoxPrivacy::FRIENDS_OF_FRIENDS,
                        MetaFoxPrivacy::ONLY_ME,
                    ],
                ],
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [];
    }
}
