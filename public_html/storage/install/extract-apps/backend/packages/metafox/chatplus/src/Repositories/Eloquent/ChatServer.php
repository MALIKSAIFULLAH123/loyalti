<?php

namespace MetaFox\ChatPlus\Repositories\Eloquent;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route as RouteFacade;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Authorization\Models\Role;
use MetaFox\Ban\Repositories\BanRuleRepositoryInterface;
use MetaFox\ChatPlus\Http\Resources\v1\User\UserItem;
use MetaFox\ChatPlus\Http\Resources\v1\User\UserItemCollection;
use MetaFox\ChatPlus\Jobs\ProcessSyncUsers;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\ChatPlus\Repositories\JobRepositoryInterface;
use MetaFox\ChatPlus\Support\Traits\ChatplusTrait;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\SEO\Models\Meta;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use RuntimeException;

/**
 * Class ChatServer.
 * @ignore
 * @codeCoverageIgnore
 */
class ChatServer implements ChatServerInterface
{
    use ChatplusTrait;

    /**
     * @var JobRepositoryInterface
     */
    private $jobRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * ChatServer constructor.
     *
     * @param JobRepositoryInterface  $jobRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(JobRepositoryInterface $jobRepository, UserRepositoryInterface $userRepository)
    {
        $this->jobRepository  = $jobRepository;
        $this->userRepository = $userRepository;
    }

    public function syncSettings(bool $skipCache = false, bool $throwError = true): void
    {
        try {
            if ($skipCache) {
                $server = Settings::get('chatplus.server');
            } else {
                $server = SiteSetting::query()
                    ->where('name', '=', 'chatplus.server')
                    ->first()?->value;
            }

            if (!$server) {
                Log::channel('installation')->error('chatplus sync ignored ', ['server' => $server, 'throwError' => $throwError, 'skipCache' => $skipCache]);

                return;
            }

            $settings = $this->getSettings($skipCache);

            $banWords                         = resolve(BanRuleRepositoryInterface::class)->getBanRulesByType('word');
            $settings['general']['ban_words'] = $banWords->map(function ($banWord) {
                return [
                    'word_id'          => $banWord->id,
                    'ban_word'         => $banWord->find_value,
                    'replacement_word' => $banWord->replacement,
                ];
            })->toArray();

            $apiUrl = $server . '/api/v1/metafox.update_settings';

            $response = Http::timeout(60)->post($apiUrl, $settings);
            if (!Arr::get($response->json(), 'data.success') && $throwError) {
                throw new RuntimeException($response->body());
            }
        } catch (Exception $e) {
            Log::channel('installation')->error('chatplus error ' . $e->getMessage());
            if ($throwError) {
                throw new RuntimeException($e->getMessage());
            }
        }
    }

    private function getGuestToken(): string
    {
        return 'chatplus_access_token';
    }

    public function getSettings(bool $skipCache = false, bool $hidePrivate = false): array
    {
        return [
            'general'     => $this->getGeneral($skipCache, $hidePrivate),
            'permissions' => $this->getPermissions(),
        ];
    }

    public function getGeneral(bool $skipCache = false, bool $hidePrivate = false): array
    {
        $siteUrl = rtrim(config('app.url'), '/');
        $apiUrl  = !config('app.mfox_api_root_url') ? (rtrim(config('app.url'), '/') . '/api/v1') : rtrim(config('app.mfox_api_url'), '/');

        if (!$skipCache) {
            localCacheStore()->clear();
            Settings::refresh();
        }
        $vals       = Settings::get('chatplus');
        $firebase   = Settings::get('firebase', []);
        $server     = $vals['server'] ?? '';
        $secretCode = $vals['private_code'] ?? '';
        $https      = request()->secure();

        $general = [
            // prevent chatplus block admin
            'Accounts_SystemBlockedUsernameList'          => 'administrator,system,user',
            'Metafox_Server_Version'                      => '5.0.1',
            'Metafox_ChatPlus_App_Version'                => '5.0.1',
            'Metafox_License_Id'                          => config('app.mfox_license_id'),
            'Site_Url'                                    => rtrim($server, '/') . '/',
            'Accounts_RegistrationForm'                   => 'Disabled',
            'Accounts_OAuth_Phpfox'                       => false,
            'ChatPlusServer_URL'                          => rtrim($server, '/'),
            'Website'                                     => $siteUrl,
            'API_Metafox_URL'                             => $apiUrl,
            'Accounts_OAuth_Metafox_callback_url'         => '/_oauth/metafox',
            'Metafox_OAuth_Guest_Access_Token'            => $this->getGuestToken(),
            'Metafox_APP_BUNDLE_ID'                       => (string) $vals['ios_bundle_id'],
            'Metafox_User_Per_Call_Limit'                 => (int) $vals['user_per_call_limit'],
            'Metafox_Call_Limit'                          => (int) $vals['call_limit'],
            'Metafox_APN_KEY_ID'                          => (string) $vals['ios_apn_key_id'],
            'Metafox_APN_TEAM_ID'                         => (string) $vals['ios_apn_team_id'],
            'Metafox_Visibility'                          => (string) $vals['chat_visibility'],
            'Enable_Discussion'                           => (bool) $vals['enable_discussion'],
            'Threads_enabled'                             => (bool) $vals['enable_thread'],
            'Metafox_Enable_Voice_Call'                   => (bool) $vals['enable_voice_call'],
            'Metafox_Enable_Video_Chat'                   => (bool) $vals['enable_video_chat'],
            'Message_AllowDeleting'                       => (bool) $vals['enable_delete_message'],
            'Message_AllowStarring'                       => (bool) $vals['enable_star_message'],
            'Message_AllowPinning'                        => (bool) $vals['enable_pin_message'],
            'Message_AllowEditing'                        => (bool) $vals['enable_edit_message'],
            'Favorite_Rooms'                              => (bool) $vals['enable_favorite_rooms'],
            'Message_AllowEditing_BlockEditInMinutes'     => (int) $vals['message_blocked_edit_in_minutes'],
            'Message_AllowDeleting_BlockDeleteInMinutes'  => (int) $vals['message_blocked_delete_in_minutes'],
            'Metafox_Jitsi_URL_Room_Prefix'               => 'f',
            'Metafox_Jitsi_Enabled'                       => true,
            'Metafox_Jitsi_Domain_Option'                 => (string) $vals['jitsi_domain_option'],
            'Metafox_Jitsi_Domain'                        => $vals['jitsi_domain'],
            'Metafox_Jitsi_SSL'                           => $https,
            'Metafox_Jitsi_Enabled_TokenAuth'             => (bool) $vals['jitsi_enable_auth'],
            'Metafox_Jitsi_Application_ID'                => (string) $vals['jitsi_application_id'],
            'Metafox_Jitsi_Limit_Token_To_Room'           => false,
            'Iframe_Integration_receive_enable'           => true,
            'Iframe_Integration_send_enable'              => true,
            'Metafox_Filter_Words'                        => '',
            'UI_Use_Real_Name'                            => true,
            'UI_DisplayRoles'                             => false,
            'UI_Group_Channels_By_Type'                   => true,
            'UI_Click_Direct_Message'                     => true,
            'UI_Allow_room_names_with_special_chars'      => true,
            'Site_Name'                                   => Settings::get('core.general.site_title'),
            'Accounts_Send_Email_When_Activating'         => false,
            'Accounts_Send_Email_When_Deactivating'       => false,
            'Accounts_EmailVerification'                  => false,
            'Accounts_ManuallyApproveNewUsers'            => false,
            'Accounts_TwoFactorAuthentication_Enabled'    => false,
            'Discussion_enabled'                          => $vals['enable_discussion'] ? true : false,
            'UI_Use_Name_Avatar'                          => true,
            'API_Enable_Rate_Limiter'                     => false,
            'DDP_Rate_Limit_IP_Enabled'                   => false,
            'DDP_Rate_Limit_User_Enabled'                 => false,
            'DDP_Rate_Limit_Connection_Enabled'           => false,
            'DDP_Rate_Limit_User_By_Method_Enabled'       => false,
            'DDP_Rate_Limit_Connection_By_Method_Enabled' => false,
            'Metafox_Firebase_SenderId'                   => Arr::get($firebase, 'sender_id'),
            'Metafox_Firebase_ServerKey'                  => Arr::get($firebase, 'server_key'),
            'Metafox_Firebase_ProjectId'                  => Arr::get($firebase, 'project_id'),
            'Metafox_Firebase_Credential'                 => json_encode([
                'client_email' => Arr::get($firebase, 'client_email'),
                'client_id'    => Arr::get($firebase, 'client_id'),
                'private_key'  => Arr::get($firebase, 'private_key'),
            ]),
            'Metafox_Ringtone_Sound_Url'     => str_replace($siteUrl, '', $this->getAssetRepository()->getUrl('metafox/chatplus', 'incoming_call_mp3') ?? '/storage/assets/chatplus/audio/incoming-call.mp3'),
            'Metafox_Notification_Sound_Url' => str_replace($siteUrl, '', $this->getAssetRepository()->getUrl('metafox/chatplus', 'notification_mp3') ?? '/storage/assets/chatplus/audio/sound_notification.mp3'),
        ];

        if (!$hidePrivate) {
            $general['Metafox_APN_KEY']                  = (string) $vals['ios_apn_key'];
            $general['Metafox_ChatPlus_Secret_Code']     = $secretCode;
            $general['Metafox_Jitsi_Application_Secret'] = (string) $vals['jitsi_application_secret'];
        }

        return $general;
    }

    private function getPermissions(): array
    {
        $permissions = [
            [
                'roles'       => ['admin', 'staff'],
                'permissions' => [
                    'delete-d',
                ],
            ],
            [
                'roles'       => ['admin', 'user', 'staff', 'owner', 'leader', 'moderator'],
                'permissions' => [
                    'create-c', 'create-p', 'create-d', 'leave-c', 'leave-p', 'delete-own-message',
                    'add-user-to-any-c-room', 'mention-all', 'mention-here', 'pin-message', 'view-history',
                    'preview-c-room', 'view-outside-room', 'view-d-room', 'view-c-room', 'view-p-room', 'start-call-c-room', 'start-call-p-room',
                    'start-call-d-room', 'start-video-chat-c-room', 'start-video-chat-p-room', 'start-video-chat-d-room',
                    'create-personal-access-tokens', 'mobile-upload-file', 'create-team', 'start-discussion', 'start-discussion-other-user',
                ],
            ],
            [
                'roles'       => ['admin', 'owner'],
                'permissions' => [
                    'delete-p', 'archive-room', 'unarchive-room', 'set-leader', 'set-moderator',
                    'set-owner', 'set-readonly', 'edit-message',
                ],
            ],
            [
                'roles'       => ['admin', 'owner', 'moderator'],
                'permissions' => [
                    'delete-c', 'delete-p', 'force-delete-message', 'delete-message',
                    'add-user-to-any-p-room', 'remove-user', 'ban-user', 'remove-team-channel',
                ],
            ],
        ];
        $childRoles = $this->getChatplusChildRoles();
        $results    = [];
        foreach ($permissions as $permission) {
            foreach ($permission['roles'] as $role) {
                if (Arr::exists($childRoles, $role)) {
                    $permission['roles'] = Arr::collapse([$permission['roles'], $childRoles[$role]]);
                }
            }
            foreach ($permission['permissions'] as $perm) {
                $results[$perm] = $permission['roles'];
            }
        }

        return $results;
    }

    public function exportUsers(int $min, int $max): void
    {
        $users = $this->userRepository->with(['profile'])->where([
            ['id', '>', $min],
            ['id', '<=', $max],
        ])->all();

        if (empty($users)) {
            return;
        }

        $data = (new UserItemCollection($users))->toArray(request());

        $this->jobRepository->addJob('onImportUsers', (array) $data);
    }

    public function syncUsers(): void
    {
        $limit = 30;

        $maxUserId = DB::table('users')->max('id');

        for ($from = 0; $from <= $maxUserId; $from += $limit) {
            ProcessSyncUsers::dispatch($from, $from + $limit);
        }
    }

    public function createRole(Role $role): void
    {
        $parentRoleName = $this->getChatplusParentRole($role);
        $permissions    = array_filter($this->getPermissions(), function ($data) use ($parentRoleName) {
            if (in_array($parentRoleName, $data)) {
                return true;
            }

            return false;
        });
        $this->jobRepository->addJob('onCreateUserGroup', [
            'group_title' => $role->name,
            'user_id'     => user()->getAuthIdentifier(),
            'role_id'     => "role-{$role->entityId()}",
            'permissions' => array_keys($permissions),
        ]);
    }

    public function deleteRole(Role $role, int $alternativeId): void
    {
        $alternativeRoleName = $this->getChatplusRole($alternativeId);
        $roleName            = $this->getChatplusRole($role->entityId());
        $this->jobRepository->addJob('onDeleteUserGroup', [
            'user_id'   => user()->getAuthIdentifier(),
            'delete_id' => $roleName[0] ?? "role-{$role->entityId()}",
            'move_to'   => $alternativeRoleName[0] ?? "role-$alternativeId",
        ]);
    }

    public function addFriend(int $id): void
    {
        $this->shouldUpdateUser('onUpdateUserInfo', $id);
    }

    public function unFriend(int $id): void
    {
        $this->shouldUpdateUser('onUpdateUserInfo', $id);
    }

    public function addUser(int $id): void
    {
        $this->shouldUpdateUser('onCreateUser', $id);
    }

    public function updateUser(int $id): void
    {
        $this->shouldUpdateUser('onUpdateUserInfo', $id);
    }

    public function deleteUser(int $id): void
    {
        $this->jobRepository->addJob('onDeleteUser', ['id' => (string) $id]);
    }

    public function blockUser(int $blockerId, int $blockedId): void
    {
        $this->shouldUpdateUser('onUpdateUserInfo', $blockerId);
        $this->shouldUpdateUser('onUpdateUserInfo', $blockedId);
        $this->jobRepository->addJob('onBlockUser', [
            'blockerId' => $blockerId,
            'blockedId' => $blockedId,
        ]);
    }

    public function unBlockUser(int $blockerId, int $blockedId): void
    {
        $this->shouldUpdateUser('onUpdateUserInfo', $blockerId);
        $this->shouldUpdateUser('onUpdateUserInfo', $blockedId);
        $this->jobRepository->addJob('onUnBlockUser', [
            'unBlockerId' => $blockerId,
            'unBlockedId' => $blockedId,
        ]);
    }

    private function shouldUpdateUser(string $job, int $id): void
    {
        $user = $this->userRepository
            ->getModel()
            ->newModelQuery()
            ->with(['profile'])
            ->where('id', '=', $id)
            ->whereNull('deleted_at')
            ->first();

        if (null === $user) {
            return;
        }

        $data = (new UserItem($user))->toArray(request());

        $this->jobRepository->addJob($job, (array) $data);
    }

    public function prefetchUsers(array $request): array
    {
        $username = $request['username'] ?? '';
        $limit    = $request['limit'] ?? 10;
        if (!$username) {
            return [];
        }
        $user = $this->userRepository->getModel()
            ->newModelQuery()
            ->where('user_name', $username)
            ->whereNull('deleted_at')
            ->first();
        if (!$user) {
            return [];
        }
        $visibility = Settings::get('chatplus.chat_visibility');
        if ($visibility == 'public') {
            $users = $this->userRepository
                ->getModel()->newModelQuery()
                ->from('users as u')
                ->with(['profile'])
                ->join('user_activities as ua', 'ua.id', '=', 'u.id')
                ->where([
                    ['u.user_name', '<>', $username],
                    ['u.approve_status', '=', MetaFoxConstant::STATUS_APPROVED],
                ])
                ->whereNull('u.deleted_at')
                ->limit($limit)
                ->orderBy('ua.last_activity', 'desc')
                ->get();
        } else {
            $friendIds = app('events')->dispatch('friend.friend_ids', [$user->entityId()], true);
            $users     = $this->userRepository
                ->getModel()->newModelQuery()
                ->from('users as u')
                ->with(['profile'])
                ->join('user_activities as ua', 'ua.id', '=', 'u.id')
                ->whereIn('u.id', $friendIds)
                ->where('u.approve_status', MetaFoxConstant::STATUS_APPROVED)
                ->whereNull('u.deleted_at')
                ->orderBy('ua.last_activity', 'desc')
                ->limit($limit)
                ->get();
        }

        return (new UserItemCollection($users))->toArray(request());
    }

    /**
     * @param  array      $request
     * @return Collection
     */
    public function loadJobs(array $request): Collection
    {
        $clear = $request['clear'] ?? 0;
        $limit = $request['limit'] ?? 10;

        return $this->jobRepository->getJobs($limit, $clear);
    }

    public function importUsers(string $resource = 'user', string $source = 'phpfox'): void
    {
        if (!app_active('metafox/importer')) {
            return;
        }
        /** @var Collection $userList */
        $userList = resolve(EntryRepositoryInterface::class)->getEntriesByResource($resource, $source);
        if (!$userList->count()) {
            return;
        }
        foreach ($userList as $item) {
            $this->jobRepository->addJob('onMigrateUsers', [
                'prefix' => $resource . '#',
                'source' => $source,
                'data'   => $item,
            ]);
        }
        $this->jobRepository->addJob('onAfterMigrateUsers', []);
    }

    public function addDeviceTokens(int $userId, array $devices, array $tokens, string $platform): void
    {
        $this->jobRepository->addJob('onAddDeviceTokens', [
            'userId'   => $userId,
            'tokens'   => $tokens,
            'platform' => $platform,
            ...$devices,
        ]);
    }

    public function removeDeviceTokens(int $userId, array $tokens): void
    {
        $this->jobRepository->addJob('onRemoveDeviceTokens', [
            'userId' => $userId,
            'tokens' => $tokens,
        ]);
    }

    public function deleteOrMoveReaction(int $reactionId, ?int $newReactionId = null): void
    {
        $this->jobRepository->addJob('onDeleteMoveReaction', [
            'reactionId'    => $reactionId,
            'newReactionId' => $newReactionId,
        ]);
    }

    public function addBanWord(int $id, ?string $banWord = null, ?string $replacementWord = null): void
    {
        if (!empty($banWord) && !empty($replacementWord)) {
            $this->jobRepository->addJob('onAddBanWord', [
                'word_id'          => $id,
                'ban_word'         => $banWord,
                'replacement_word' => $replacementWord,
            ]);
        }
    }

    public function deleteBanWord(int $id): void
    {
        $this->jobRepository->addJob('onDeleteBanWord', [
            'word_id' => $id,
        ]);
    }

    public function disableChatPlus(string $package, bool $optimizeClear = true): void
    {
        try {
            $otherPackage = resolve(PackageRepositoryInterface::class)->getModel()
                ->newQuery()->where([
                    'name' => $package,
                ])->first();

            if (!$otherPackage || !$otherPackage->is_active) {
                return;
            }

            $package = resolve(PackageRepositoryInterface::class)->getModel()
                ->newQuery()->where([
                    'name' => 'metafox/chatplus',
                ])->first();

            if (!$package) {
                return;
            }

            $package->is_active = 0;

            $package->save();

            $package->refresh();

            if ($optimizeClear) {
                Artisan::call('optimize:clear');
            }
        } catch (Exception) {
            // Silent error
        }
    }

    public function enableChatPlus(bool $optimizeClear = true): void
    {
        try {
            $package = resolve(PackageRepositoryInterface::class)->getModel()
                ->newQuery()->where([
                    'name' => 'metafox/chatplus',
                ])->first();

            if (!$package) {
                return;
            }

            $package->is_active = 1;

            $package->save();

            $package->refresh();

            if ($optimizeClear) {
                Artisan::call('optimize:clear');
            }
        } catch (Exception) {
            // Silent error
        }
    }

    public function importConversations(array $data): void
    {
        $this->jobRepository->addJob('onImportConversation', $data);
    }

    public function getInternalUrlMetadata(string $url): ?array
    {
        if (!url_utility()->isAppUrl($url)) {
            return null;
        }

        $route = $this->getRoute($url);

        if (!$route) {
            return null;
        }
        $meta = $this->getMeta($route->uri);

        if (!$meta instanceof Meta) {
            return null;
        }

        $resource = ResourceGate::getItem($meta->item_type, Arr::get($route->parameters(), 'id'));

        if (!$resource instanceof Entity
            || !$resource instanceof HasPrivacy
            || $resource->privacy !== MetaFoxPrivacy::MEMBERS
        ) {
            return null;
        }
        $siteTitle = Settings::get('core.general.site_title');
        $title     = $resource->title;

        if (!$title && method_exists($resource, 'toTitle')) {
            $title = $resource->toTitle();
        }

        $title    = parse_output()->limit($title, MetaFoxConstant::DEFAULT_MAX_SEO_TITLE_LENGTH);
        $ogTitle  = rtrim(trim($title ? $title : $siteTitle), '.');

        $description = null;
        if (method_exists($resource, 'toOGDescription')) {
            $description = $resource->toOGDescription();
            $description = $description ? strip_tags(substr($description ?? '', 0, 225)) : null;
        }

        if (!$description) {
            $description = $meta->description;
        }
        if (!$description) {
            $description = Settings::get('core.general.description');
        }
        $image  = null;
        $images = $resource->images;

        $customImages = [];

        if (method_exists($resource, 'toOGImages')) {
            $customImages = $resource->toOGImages();
        }

        if (!empty($customImages)) {
            $images = $customImages;
        }

        $preferSizes = ['1024', 'origin'];

        if (is_array($images)) {
            foreach ($preferSizes as $size) {
                if (isset($images[$size])) {
                    $image = $images[$size];
                    break;
                }
            }
        }
        if (!$image) {
            $defaultLogo = resolve(AssetRepositoryInterface::class)
                ->getModel()
                ->newModelQuery()
                ->where('name', 'image_welcome')
                ->where('package_id', 'metafox/layout')
                ->first();

            $image = $defaultLogo?->url ?? $resource->image;
        }

        return [
            'pageTitle'     => $title,
            'ogTitle'       => $ogTitle,
            'ogDescription' => htmlspecialchars($description),
            'ogImageWidth'  => '600',
            'ogImageHeight' => '315',
            'ogImage'       => $image,
            'ogImageAlt'    => $image,
            'twitterCard'   => 'summary',
            'twitterImage'  => $image,
        ];
    }

    private function getRoute(string $url): ?Route
    {
        try {
            $request = Request::create($this->addSharingPrefix($url));

            return RouteFacade::getRoutes()->match($request);
        } catch (\Exception $exception) {
            Log::channel('dev')->error('Route not found', [$exception->getMessage()]);

            return null;
        }
    }

    private function addSharingPrefix(string $url): string
    {
        $urlComponents = parse_url($url);
        $path          = isset($urlComponents['path']) ? ('/' . ltrim($urlComponents['path'], '/')) : '';

        return sprintf(
            '%s://%s/sharing%s',
            $urlComponents['scheme'],
            $urlComponents['host'],
            $path,
        );
    }

    private function getMeta(string $url): ?Meta
    {
        $url = str_replace('sharing/', '', $url);

        return Meta::query()
            ->where('resolution', 'web')
            ->where('url', '=', $url)
            ->first();
    }
}
