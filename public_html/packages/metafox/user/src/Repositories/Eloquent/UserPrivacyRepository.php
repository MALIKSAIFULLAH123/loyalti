<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserPrivacy;
use MetaFox\User\Models\UserPrivacyResource;
use MetaFox\User\Models\UserPrivacyType;
use MetaFox\User\Repositories\Eloquent\UserPrivacyTraits\CollectItemPrivacySetting;
use MetaFox\User\Repositories\Eloquent\UserPrivacyTraits\CollectProfileMenuPrivacySetting;
use MetaFox\User\Repositories\Eloquent\UserPrivacyTraits\CollectProfilePrivacySetting;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User as UserSupport;

/**
 * Class UserPrivacyRepository.
 *
 * @property UserPrivacy $model
 * @method   UserPrivacy getModel()
 */
class UserPrivacyRepository extends AbstractRepository implements UserPrivacyRepositoryInterface
{
    use CollectItemPrivacySetting;
    use CollectProfilePrivacySetting;
    use CollectProfileMenuPrivacySetting;

    public const CACHE_NAME_USER_PRIVACY_TYPES = 'user_privacy_types';
    public const CACHE_NAME_USER_PRIVACY       = 'user_privacy';
    public const CACHE_LIFETIME                = 3000;

    public function model(): string
    {
        return UserPrivacy::class;
    }

    /**
     * Mass collect from all packages.
     *
     * @param string $name
     *
     * @return array<string, array<string, mixed>>
     */
    protected function massCollect(string $name): array
    {
        $response = PackageManager::discoverSettings($name);

        if (empty($response) || !is_array($response)) {
            return [];
        }

        return $response;
    }

    private function updatePrivacyType(string $name, int $value): void
    {
        UserPrivacyType::query()->updateOrCreate([
            'name' => $name,
        ], [
            'name'            => $name,
            'privacy_default' => $value,
        ]);
    }

    public function installPrivacyTypes(): void
    {
        // Install user privacy.
        $userPrivacy = $this->collectUserPrivacyType();
        if (!empty($userPrivacy)) {
            foreach ($userPrivacy as $userPrivacyName => $userPrivacyData) {
                $this->updatePrivacyType($userPrivacyName, $userPrivacyData['default']);
            }
        }

        $userPrivacyResources = $this->collectProfilePrivacySetting();
        foreach ($userPrivacyResources as $entityType => $settings) {
            foreach ($settings as $settingName => $value) {
                $this->updatePrivacyResourceByName($entityType, $settingName, $value);
            }
        }

        // Install {resource}.item_privacy.
        $resourcePrivacy = $this->collectItemPrivacySetting();
        if (!empty($resourcePrivacy)) {
            foreach ($resourcePrivacy as $resourcePrivacyName => $resourcePrivacyData) {
                $this->updatePrivacyType($resourcePrivacyName, $resourcePrivacyData['default']);
            }
        }

        // Install module.profile_menu.
        $profileMenuPrivacy = $this->collectProfileMenuSetting();
        if (!empty($profileMenuPrivacy)) {
            foreach ($profileMenuPrivacy as $menuPrivacyName => $menuData) {
                $this->updatePrivacyType($menuPrivacyName, $menuData['default']);
            }
        }
    }

    public function getPrivacyTypes(): array
    {
        return localCacheStore()->rememberForever(
            __FUNCTION__,
            fn() => UserPrivacyType::query()->get()->keyBy('name')->toArray()
        );
    }

    public function getPrivacyTypesByEntity(string $entityType): array
    {
        return localCacheStore()->rememberForever(
            'getPrivacyTypesByEntity_' . $entityType,
            fn() => UserPrivacyResource::query()
                ->where('entity_type', $entityType)
                ->get()
                ->keyBy('type_id')
                ->toArray()
        );
    }

    public function updateUserPrivacy(int $userId, array $params): array
    {
        $data         = [];
        $privacyTypes = $this->getPrivacyTypes();
        if (!empty($params)) {
            foreach ($params as $privacyName => $privacy) {
                $privacyName = $this->convertPrivacySettingName($privacyName);
                if (!array_key_exists($privacyName, $privacyTypes)) {
                    continue;
                }

                // @todo if privacy friend_of_friends, store privacy_id of user_friends ??

                $seekPrivacy = $privacy;
                if ($privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS) {
                    $seekPrivacy = MetaFoxPrivacy::FRIENDS;
                }

                $privacyId = app('events')
                    ->dispatch('core.user_privacy.get_privacy_id', [$userId, $seekPrivacy], true);

                if (!$privacyId) {
                    continue;
                }
                $this->getModel()->newInstance()->updateOrCreate([
                    'user_id' => $userId,
                    'type_id' => $privacyTypes[$privacyName]['id'],
                ], [
                    'user_id'    => $userId,
                    'type_id'    => $privacyTypes[$privacyName]['id'],
                    'name'       => $privacyName,
                    'privacy'    => $privacy,
                    'privacy_id' => $privacyId,
                ]);
                $data[$privacyName] = $privacy;
            }
        }

        return $data;
    }

    /**
     * @param User  $user
     * @param array $params
     *
     * @return void
     */
    public function updateOtherUserPrivacy(User $user, array &$params): void
    {
        $otherPrivacies = [
            UserSupport::USER_PROFILE_DATE_OF_BIRTH_FORMAT,
            UserSupport::AUTO_APPROVED_TAGGED_SETTING,
        ];

        foreach ($otherPrivacies as $otherPrivacy) {
            if (!Arr::has($params, $otherPrivacy)) {
                continue;
            }

            UserValue::updateUserValueSetting(
                $user,
                [$otherPrivacy => $params[$otherPrivacy]]
            );
        }

        Arr::forget($params, $otherPrivacies);
    }

    public function deleteUserPrivacy(int $userId): int
    {
        return $this->deleteWhere(['user_id' => $userId]);
    }

    public function getUserPrivacy(int $userId): array
    {
        return LoadReduce::remember(
            sprintf('privacy::all(user:%s)', $userId),
            fn() => $this->findWhere(['user_id' => $userId])->keyBy('name')->toArray()
        );
    }

    public function getItemPrivacySettings(int $userId): array
    {
        $user        = UserEntity::getById($userId);
        $settingData = [];
        $settings    = $this->mapUserPrivacy(
            $user->entityId(),
            $user->entityType(),
            $this->collectItemPrivacySetting()
        );

        foreach ($settings as $settingName => $setting) {
            if (!Arr::has($setting, 'module_id') || !app_active($setting['module_id'])) {
                continue;
            }

            $settingData[] = array_merge($setting, [
                'var_name'  => $settingName,
                'options'   => array_values($setting['options']),
                'custom_id' => str_replace('.', '_', $settingName),
            ]);
        }

        return $settingData;
    }

    public function getItemPrivacySettingsForValidate(int $userId): array
    {
        $user     = UserEntity::getById($userId);
        $settings = $this->mapUserPrivacy(
            $user->entityId(),
            $user->entityType(),
            $this->collectItemPrivacySetting()
        );

        return collect($settings)->filter(fn($x) => Arr::has($x, 'module_id') && app_active($x['module_id']))->toArray();
    }

    public function getProfileMenuSettings(int $userId): array
    {
        $settingData = [];
        $user        = UserEntity::getById($userId);
        $settings    = $this->mapUserPrivacy(
            $user->entityId(),
            $user->entityType(),
            $this->collectProfileMenuSetting()
        );

        foreach ($settings as $settingName => $setting) {
            if (!Arr::get($setting, 'is_editable')) {
                continue;
            }

            $setting = array_merge($setting, [
                'var_name' => $settingName,
                'options'  => array_values($setting['options']),
            ]);

            $settingData[] = $setting;
        }

        return $settingData;
    }

    public function getProfileMenuSettingsForValidate(int $userId): array
    {
        $user        = UserEntity::getById($userId);
        $settingData = [];

        $settings = $this->mapUserPrivacy(
            $user->entityId(),
            $user->entityType(),
            $this->collectProfileMenuSetting()
        );

        foreach ($settings as $settingName => $setting) {
            if (!Arr::get($setting, 'is_editable')) {
                continue;
            }

            $settingData[$settingName] = $setting;
        }

        return $settingData;
    }

    public function getProfileSettings(int $userId): array
    {
        $user        = UserEntity::getById($userId);
        $settingData = [];

        $settings = $this->mapUserPrivacy(
            $user->entityId(),
            $user->entityType(),
            $this->collectProfilePrivacySettingByEntity($user->entityType())
        );

        foreach ($settings as $settingName => $setting) {
            $setting       = array_merge($setting, [
                'var_name' => $settingName,
                'options'  => array_values($setting['options']),
            ]);
            $settingData[] = $setting;
        }

        return $settingData;
    }

    public function getProfileSettingsForValidate(int $userId): array
    {
        $user = UserEntity::getById($userId);

        return $this->mapUserPrivacy(
            $user->entityId(),
            $user->entityType(),
            $this->collectProfilePrivacySettingByEntity($user->entityType())
        );
    }

    /**
     * 1. Collect all app first.
     * 2. Map to user setting (user_privacy_values).
     * 3. If not, use privacy_default in user_privacy_types.
     *
     * @param int                  $userId
     * @param string               $userType
     * @param array<string, mixed> $privacyCollection
     *
     * @return array<string, mixed>
     */
    public function mapUserPrivacy(int $userId, string $userType, array $privacyCollection): array
    {
        $userPrivacy = $this->getUserPrivacy($userId);

        $privacyTypesEntity = $this->getPrivacyTypesByEntity($userType);
        $privacyTypes       = $this->getPrivacyTypes();
        $results            = [];

        foreach ($privacyCollection as $name => $data) {
            $result = [
                'module_id'   => $data['module_id'],
                'value'       => $data['default'],
                'phrase'      => __p($data['phrase']),
                'options'     => $data['options'],
                'is_editable' => $data['is_editable'] ?? true,
            ];

            // If user had set setting, use it value.
            if (array_key_exists($name, $userPrivacy)) {
                $result['value'] = $userPrivacy[$name]['privacy'];
                $results[$name]  = $result;
                continue;
            }

            // If user had set setting, use it value.
            if (array_key_exists($name, $privacyTypesEntity)) {
                $result['value'] = $privacyTypesEntity[$name]['privacy_default'];
                $results[$name]  = $result;
                continue;
            }

            // If admin had set default value, use value from database.
            if (array_key_exists($name, $privacyTypes)) {
                $result['value'] = $privacyTypes[$name]['privacy_default'];
                $results[$name]  = $result;
                continue;
            }
            // Otherwise use app default_value.
            $results[$name] = $result;
        }

        return $results;
    }

    public function updatePrivacyResourceValueByEntity(string $entityType, int $value): void
    {
        UserPrivacyResource::query()
            ->where('entity_type', '=', $entityType)
            ->update([
                'privacy_default' => $value,
            ]);
    }

    /**
     * @param string               $entityType
     * @param string               $settingName
     * @param array<string, mixed> $value
     *
     * @return void
     */
    private function updatePrivacyResourceByName(string $entityType, string $settingName, array $value): void
    {
        UserPrivacyResource::query()->updateOrCreate([
            'entity_type' => $entityType,
            'type_id'     => $settingName,
        ], [
            'entity_type'     => $entityType,
            'type_id'         => $settingName,
            'phrase'          => $value['phrase'],
            'privacy_default' => $value['default'],
        ]);
    }

    public function convertPrivacySettingName(string $privacyName): string
    {
        return str_replace('.', MetaFoxConstant::SEPARATION_PERM, $privacyName);
    }

    /**
     * @inheritDoc
     */
    public function getBirthdaySetting(User $user): array
    {
        $settingName = 'user_profile_date_of_birth_format';

        $options = $this->getBirthdayOptionsForForm();

        $birthdaySetting = UserValue::getUserValueSettingByName($user, $settingName) ?? 1;

        return [
            'module_id' => 'user',
            'value'     => $birthdaySetting,
            'phrase'    => __p('user::phrase.date_of_birth_format'),
            'var_name'  => $settingName,
            'options'   => $options,
        ];
    }

    public function getBirthdayOptionsForForm(bool $isAdmin = false): array
    {
        $config = config('birthday', []);

        if (!is_array($config)) {
            return [];
        }

        $phrase = $isAdmin ? 'admin_phrase' : 'phrase';

        return collect($config)->keyBy('value')->map(function ($option, $key) use ($phrase) {
            return [
                'label' => __p($option[$phrase] ?? null),
                'value' => $key,
            ];
        })->values()->toArray();
    }

    public function getUserPrivacyByName(int $userId, string $name): ?UserPrivacy
    {
        $name = $this->convertPrivacySettingName($name);

        /** @todo optimize using cache key */
        return $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
                'name'    => $name,
            ])
            ->first();
    }
}
