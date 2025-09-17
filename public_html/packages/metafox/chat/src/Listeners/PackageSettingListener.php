<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Models\Message;
use MetaFox\Chat\Models\Room;
use MetaFox\Chat\Notifications\NewMessageNotification;
use MetaFox\Chat\Policies\MessagePolicy;
use MetaFox\Chat\Policies\RoomPolicy;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

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
    public function getEvents(): array
    {
        return [
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'user.permissions.extra' => [
                UserExtraPermissionListener::class,
            ],
            'user.deleted' => [
                UserDeletedListener::class,
            ],
            'chat.message.active' => [
                ChatActiveListener::class,
            ],
            'chat.room.create' => [
                ChatRoomCreateListener::class,
            ],
            'chat.message.create' => [
                ChatMessageCreateListener::class,
            ],
            'user.unblocked' => [
                UnBlockUserListener::class,
            ],
            'user.blocked' => [
                BlockUserListener::class,
            ],
            'site_settings.updated' => [
                SiteSettingUpdatedListener::class,
            ],
            'like.delete_or_move_reaction' => [
                DeleteOrMoveReactionListener::class,
            ],
            'packages.activated' => [
                PackageActivatedListener::class,
            ],
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'core.badge_counter' => [
                GetNewNotificationCount::class,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [];
    }

    public function getPolicies(): array
    {
        return [
            Room::class    => RoomPolicy::class,
            Message::class => MessagePolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Message::ENTITY_TYPE => [
                'create'          => UserRole::LEVEL_REGISTERED,
                'update'          => UserRole::LEVEL_REGISTERED,
                'delete'          => UserRole::LEVEL_REGISTERED,
                'send_attachment' => UserRole::LEVEL_REGISTERED,
            ],
            Room::ENTITY_TYPE => [
                'delete' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Message::ENTITY_TYPE => [
                'attachment_type_allow' => [
                    'type'    => MetaFoxDataType::STRING,
                    'default' => 'image/*',
                ],
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'new_message',
                'module_id'  => 'chat',
                'title'      => 'chat::phrase.new_message_notification_type',
                'handler'    => NewMessageNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
        ];
    }
}
