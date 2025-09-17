<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Notifications\NewAlbumToFollowerNotification;
use MetaFox\Music\Notifications\SongApproveNotification;
use MetaFox\Music\Policies\AlbumPolicy;
use MetaFox\Music\Policies\PlaylistPolicy;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 *
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [
            [
                'type'            => Song::ENTITY_TYPE,
                'entity_type'     => Song::ENTITY_TYPE,
                'is_active'       => true,
                'title'           => 'music::phrase.song_type',
                'description'     => 'added_a_song',
                'is_system'       => 0,
                'can_comment'     => true,
                'can_like'        => true,
                'can_share'       => true,
                'can_edit'        => false,
                'can_create_feed' => true,
            ],
            [
                'type'            => Album::ENTITY_TYPE,
                'entity_type'     => Album::ENTITY_TYPE,
                'is_active'       => true,
                'title'           => 'music::phrase.album_type',
                'description'     => 'created_a_music_album',
                'is_system'       => 0,
                'can_comment'     => true,
                'can_like'        => true,
                'can_share'       => true,
                'can_edit'        => false,
                'can_create_feed' => true,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Song::ENTITY_TYPE     => [
                'view'                        => UserRole::LEVEL_GUEST,
                'create'                      => UserRole::LEVEL_PAGE,
                'update'                      => UserRole::LEVEL_PAGE,
                'delete'                      => UserRole::LEVEL_PAGE,
                'moderate'                    => UserRole::LEVEL_STAFF,
                'feature'                     => [
                    'roles'     => UserRole::LEVEL_PAGE,
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
                'auto_approved'               => UserRole::LEVEL_PAGE,
                'download'                    => UserRole::LEVEL_REGISTERED,
                'download_attachment'         => UserRole::LEVEL_REGISTERED,
            ],
            Album::ENTITY_TYPE    => [
                'view'                        => UserRole::LEVEL_GUEST,
                'create'                      => UserRole::LEVEL_PAGE,
                'update'                      => UserRole::LEVEL_PAGE,
                'delete'                      => UserRole::LEVEL_PAGE,
                'moderate'                    => UserRole::LEVEL_STAFF,
                'feature'                     => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'purchase_feature'            => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
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
                'download_attachment'         => UserRole::LEVEL_REGISTERED,
            ],
            Playlist::ENTITY_TYPE => [
                'view'                => UserRole::LEVEL_GUEST,
                'create'              => UserRole::LEVEL_PAGE,
                'update'              => UserRole::LEVEL_PAGE,
                'delete'              => UserRole::LEVEL_PAGE,
                'moderate'            => UserRole::LEVEL_STAFF,
                'save'                => UserRole::LEVEL_REGISTERED,
                'like'                => UserRole::LEVEL_REGISTERED,
                'share'               => UserRole::LEVEL_REGISTERED,
                'comment'             => UserRole::LEVEL_REGISTERED,
                'report'              => UserRole::LEVEL_REGISTERED,
                'download_attachment' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'music.view_browse_musics' => [
                'phrase' => 'music::phrase.user_privacy.who_can_view_browse_music',
            ],
            'music.share_musics'       => [
                'phrase' => 'music::phrase.user_privacy.who_can_share_music',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page'  => [
                'music.view_browse_musics',
                'music.share_musics',
            ],
            'group' => [
                'music.share_musics',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [
            Song::ENTITY_TYPE     => [
                'phrase'  => 'music::phrase.music_song_label_privacy',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            Album::ENTITY_TYPE    => [
                'phrase'  => 'music::phrase.music_album_label_privacy',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            Playlist::ENTITY_TYPE => [
                'phrase'  => 'music::phrase.music_playlist_label_privacy',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'music_song.song_default_genre'      => ['value' => 1],
            'music_song.minimum_name_length'     => ['value' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH],
            'music_song.maximum_name_length'     => ['value' => 100],
            'music_album.minimum_name_length'    => ['value' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH],
            'music_album.maximum_name_length'    => ['value' => 100],
            'music_playlist.minimum_name_length' => ['value' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH],
            'music_playlist.maximum_name_length' => ['value' => 100],
            'music_song.auto_play'               => ['value' => true],
            'music_song.purchase_sponsor_price'  => [
                'value'     => '',
                'is_public' => false,
            ],
            'music_album.purchase_sponsor_price' => [
                'value'     => '',
                'is_public' => false,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Song::class     => SongPolicy::class,
            Album::class    => AlbumPolicy::class,
            Playlist::class => PlaylistPolicy::class,
        ];
    }

    public function getProfileMenu(): array
    {
        return [
            'music' => [
                'phrase'  => 'music::phrase.music',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'like.notification_to_callback_message'    => [
                LikeNotificationMessageListener::class,
            ],
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'importer.completed'                       => [
                ImporterCompleted::class,
            ],
            'models.notify.approved'                   => [
                ModelApprovedListener::class,
            ],
            'user.deleted'                             => [
                UserDeletedListener::class,
            ],
            'core.collect_total_items_stat'            => [
                CollectTotalItemsStatListener::class,
            ],
            'advertise.sponsor.enable_sponsor_feed'    => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed'   => [
                DisableSponsorFeedListener::class,
            ],
            'activity.update_feed_item_privacy'        => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'activity.feed.deleted'                    => [
                FeedDeletedListener::class,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Song::ENTITY_TYPE     => [
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
                'maximum_number_of_songs_per_upload'                  => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 10,
                    'roles'   => [
                        UserRole::SUPER_ADMIN_USER => 0,
                        UserRole::ADMIN_USER       => 0,
                        UserRole::STAFF_USER       => 0,
                        UserRole::NORMAL_USER      => 10,
                    ],
                ],
            ],
            Album::ENTITY_TYPE    => [
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
            Playlist::ENTITY_TYPE => [
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

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['music_album', 'music_playlist', 'music_song', 'music_genre'];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('music::phrase.music_songs'),
                'value' => 'music_song',
            ],
            [
                'label' => __p('music::phrase.music_albums'),
                'value' => 'music_album',
            ],
            [
                'label' => __p('music::phrase.music_playlists'),
                'value' => 'music_playlist',
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'song_approve_notification',
                'module_id'  => 'music',
                'handler'    => SongApproveNotification::class,
                'title'      => 'music::phrase.song_approve_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 18,
            ],
            [
                'type'              => 'music_album_follower_notification',
                'module_id'         => 'music',
                'require_module_id' => 'follow',
                'handler'           => NewAlbumToFollowerNotification::class,
                'title'             => 'music::phrase.music_album_follower_notification_type',
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'          => 18,
            ],
        ];
    }

    /**
     * @return string[]|null
     */
    public function getSiteStatContent(): ?array
    {
        return [
            Song::ENTITY_TYPE     => ['icon' => 'ico-music-note'],
            Playlist::ENTITY_TYPE => ['icon' => 'ico-music-list'],
            Album::ENTITY_TYPE    => ['icon' => 'ico-music-album'],
            'pending_song'        => [
                'icon' => 'ico-clock-o',
                'to'   => '/music/music-song/browse?view=' . Browse::VIEW_PENDING,
            ],
        ];
    }
}
