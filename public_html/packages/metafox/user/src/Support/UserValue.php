<?php

namespace MetaFox\User\Support;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\PackageManager;
use MetaFox\User\Contracts\Support\UserValueInterface;
use MetaFox\User\Models\UserValue as UserValueModel;

/**
 * Class UserPrivacy.
 *
 * @see     Facades\UserValue
 * @todo    should cache.
 */
class UserValue implements UserValueInterface
{
    /**
     * @return array<string, mixed>
     */
    private function collectUserValues(): array
    {
        $response = PackageManager::discoverSettings('getUserValues');

        if (empty($response) || !is_array($response)) {
            return [];
        }

        $settings = [];
        foreach ($response as $moduleData) {
            foreach ($moduleData as $entityName => $data) {
                $existedData           = Arr::get($settings, $entityName, []);
                $settings[$entityName] = array_merge($existedData, $data);
            }
        }

        return $settings;
    }

    public function getUserValueSettings(User $user): array
    {
        $settings = $this->collectUserValues();

        if (!array_key_exists($user->entityType(), $settings)) {
            return [];
        }

        $userValues = UserValueModel::query()
            ->where('user_id', $user->entityId())
            ->get()
            ->pluck([], 'name')
            ->toArray();

        $settingData = [];
        foreach ($settings[$user->entityType()] as $name => $setting) {
            $settingData[$name] = array_merge($setting, [
                'value'  => $setting['default_value'],
                'phrase' => __p("{$user->entityType()}::phrase.{$name}"),
            ]);

            if (array_key_exists($name, $userValues)) {
                $settingData[$name]['value'] = $userValues[$name]['value'];
            }
        }

        return $settingData;
    }

    public function checkUserValueSettingByName(User $user, string $settingName): bool
    {
        $settings = $this->getUserValueSettings($user);
        if (!array_key_exists($settingName, $settings)) {
            return true;
        }

        return (bool) $settings[$settingName]['value'];
    }

    public function getUserValueSettingByName(User $user, string $settingName): ?int
    {
        $settings = $this->getUserValueSettings($user);

        if (!array_key_exists($settingName, $settings)) {
            return null;
        }

        return (int) $settings[$settingName]['value'];
    }

    public function updateUserValueSetting(User $user, array $params): bool
    {
        $settings = $this->getUserValueSettings($user);

        foreach ($params as $key => $value) {
            if (!array_key_exists($key, $settings)) {
                throw ValidationException::withMessages([
                    __p('user::validation.the_setting_invalid', ['attribute' => $key]),
                ]);
            }
        }

        foreach ($params as $key => $value) {
            UserValueModel::query()->updateOrCreate([
                'name'    => $key,
                'user_id' => $user->entityId(),
            ], [
                'name'          => $key,
                'user_id'       => $user->entityId(),
                'user_type'     => $user->entityType(),
                'value'         => $value,
                'default_value' => $settings[$key]['default_value'],
                'ordering'      => $settings[$key]['ordering'],
            ]);
        }

        return true;
    }
}
