<?php

namespace MetaFox\ChatPlus\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Authorization\Models\Role;

interface ChatServerInterface
{
    /**
     * sync settings to ChatPlus server.
     */
    public function syncSettings(bool $skipCache = false, bool $throwError = true): void;

    /**
     * export user to jobs table.
     */
    public function syncUsers(): void;

    /**
     * @param  bool  $skipCache
     * @param  bool  $hidePrivate
     * @return array
     */
    public function getSettings(bool $skipCache = false, bool $hidePrivate = false): array;

    /**
     * @param  int  $min
     * @param  int  $max
     * @return void
     */
    public function exportUsers(int $min, int $max): void;

    /**
     * @param  Role $role
     * @return void
     */
    public function createRole(Role $role): void;

    /**
     * @param  Role $role
     * @param  int  $alternativeId
     * @return void
     */
    public function deleteRole(Role $role, int $alternativeId): void;

    /**
     * @param  int  $id
     * @return void
     */
    public function addFriend(int $id): void;

    /**
     * @param  int  $id
     * @return void
     */
    public function unFriend(int $id): void;

    /**
     * @param  int  $id
     * @return void
     */
    public function addUser(int $id): void;

    /**
     * @param  int  $id
     * @return void
     */
    public function updateUser(int $id): void;

    /**
     * @param  int  $id
     * @return void
     */
    public function deleteUser(int $id): void;

    /**
     * @param  int  $blockerId
     * @param  int  $blockedId
     * @return void
     */
    public function blockUser(int $blockerId, int $blockedId): void;

    /**
     * @param  int  $blockerId
     * @param  int  $blockedId
     * @return void
     */
    public function unBlockUser(int $blockerId, int $blockedId): void;

    /**
     * @param  array $request
     * @return mixed
     */
    public function prefetchUsers(array $request): array;

    /**
     * @param  array      $request
     * @return Collection
     */
    public function loadJobs(array $request): Collection;

    /**
     * @param  string $resource
     * @param  string $source
     * @return void
     */
    public function importUsers(string $resource = 'user', string $source = 'phpfox'): void;

    /**
     * @param  int    $userId
     * @param  array  $devices
     * @param  array  $tokens
     * @param  string $platform
     * @return void
     */
    public function addDeviceTokens(int $userId, array $devices, array $tokens, string $platform): void;

    /**
     * @param  int   $userId
     * @param  array $tokens
     * @return void
     */
    public function removeDeviceTokens(int $userId, array $tokens): void;

    public function deleteOrMoveReaction(int $reactionId, ?int $newReactionId = null): void;

    public function addBanWord(int $id, ?string $banWord = null, ?string $replacementWord = null): void;

    public function deleteBanWord(int $id): void;

    public function enableChatPlus(bool $optimizeClear = true): void;

    public function importConversations(array $data): void;

    public function getInternalUrlMetadata(string $url): ?array;
}
