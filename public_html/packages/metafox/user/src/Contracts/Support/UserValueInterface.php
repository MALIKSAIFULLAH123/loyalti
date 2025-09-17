<?php

namespace MetaFox\User\Contracts\Support;

use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;

/**
 * Interface UserValueInterface.
 */
interface UserValueInterface
{
    /**
     * @param User $user
     *
     * @return array<string, mixed>
     */
    public function getUserValueSettings(User $user): array;

    /**
     * @param User   $user
     * @param string $settingName
     *
     * @return bool
     */
    public function checkUserValueSettingByName(User $user, string $settingName): bool;

    /**
     * @param User               $user
     * @param array<string, int> $params
     *
     * @return bool
     * @throws ValidationException
     */
    public function updateUserValueSetting(User $user, array $params): bool;

    /**
     * @param  User     $user
     * @param  string   $settingName
     * @return int|null
     */
    public function getUserValueSettingByName(User $user, string $settingName): ?int;
}
