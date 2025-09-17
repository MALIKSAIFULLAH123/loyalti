<?php

namespace MetaFox\User\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserPrivacy;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserPrivacy.
 *
 * @mixin BaseRepository
 */
interface UserPrivacyRepositoryInterface
{
    /**
     * Install privacy types.
     *
     * @return void
     */
    public function installPrivacyTypes(): void;

    /**
     * @return array<string, mixed>
     */
    public function getPrivacyTypes(): array;

    /**
     * Update user privacy.
     *
     * @param int                  $userId
     * @param array<string, mixed> $params [profile.view_profile => 0, profile.profile_info => 0]
     *
     * @return array<string, mixed> [profile.view_profile => 1, profile.profile_info => 1]
     */
    public function updateUserPrivacy(int $userId, array $params): array;

    /**
     * Update user privacy resource value by entity type.
     *
     * @param string $entityType
     * @param int    $value
     *
     * @return void
     */
    public function updatePrivacyResourceValueByEntity(string $entityType, int $value): void;

    /**
     * Delete user privacy when deleting a user.
     *
     * @param int $userId
     *
     * @return int
     */
    public function deleteUserPrivacy(int $userId): int;

    /**
     * Get user privacy.
     *
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    public function getUserPrivacy(int $userId): array;

    /**
     * Get Item default privacy list of an user. [ setting_name => 0 ].
     *
     * @param int $userId
     *
     * @return array<int, mixed>
     */
    public function getItemPrivacySettings(int $userId): array;

    /**
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    public function getItemPrivacySettingsForValidate(int $userId): array;

    /**
     * Get profile menu setting of an user. [ setting_name => 0 ].
     *
     * @param int $userId
     *
     * @return array<int, mixed>
     */
    public function getProfileMenuSettings(int $userId): array;

    /**
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    public function getProfileMenuSettingsForValidate(int $userId): array;

    /**
     * Get profile setting of an user. Support page/group/ v.v... [ setting_name => 0 ].
     *
     * @param int $userId
     *
     * @return array<int, mixed>
     */
    public function getProfileSettings(int $userId): array;

    /**
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    public function getProfileSettingsForValidate(int $userId): array;

    /**
     * Collect profile privacy setting based on entity name.
     *
     * @param string $entityType
     *
     * @return array<string,mixed>
     */
    public function collectProfilePrivacySettingByEntity(string $entityType): array;

    /**
     * Collect {resource_name}.profile_menu.
     * [
     *      'privacy_name' => [
     *            'phrase' => 'abc',
     *            'default' => 0,
     *            'list' => [
     *                  0 => Everyone,
     *                  1 => Friend,
     *            ]
     *       ]
     * ].
     * @return array<string, mixed>
     */
    public function collectProfileMenuSetting(): array;

    /**
     * @param string $entityType
     *
     * @return array<string, mixed>
     */
    public function getPrivacyTypesByEntity(string $entityType): array;

    /**
     * @param string $privacyName
     *
     * @return string
     */
    public function convertPrivacySettingName(string $privacyName): string;

    /**
     * Get Date of Birth setting of an user.
     *
     * @param User $user
     *
     * @return array<string, mixed>
     */
    public function getBirthdaySetting(User $user): array;

    /**
     * @param bool $isAdmin
     *
     * @return array<string, mixed>
     */
    public function getBirthdayOptionsForForm(bool $isAdmin): array;

    /**
     * @param  int              $userId
     * @param  string           $name
     * @return UserPrivacy|null
     */
    public function getUserPrivacyByName(int $userId, string $name): ?UserPrivacy;

    /**
     * @param User $user
     * @param array         $params
     *
     * @return void
     */
    public function updateOtherUserPrivacy(User $user, array &$params): void;
}
