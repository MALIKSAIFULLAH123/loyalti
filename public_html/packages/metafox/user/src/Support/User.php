<?php

namespace MetaFox\User\Support;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as SystemRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as CollectionSupport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Localize\Repositories\TimezoneRepositoryInterface;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;
use MetaFox\User\Contracts\UserContract;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Models\UserActivity;
use MetaFox\User\Models\UserGender;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Models\UserStatsActivity;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\UserRelationRepositoryInterface;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Traits\UserLocationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class User implements UserContract
{
    // todo DO NOT MIX TRAIT HERE.
    use UserLocationTrait;

    public const MENTION_REGEX                 = '^\[user=(.*?)\]^';
    public const AUTO_APPROVED_TAGGER_POST     = 0;
    public const NOT_AUTO_APPROVED_TAGGER_POST = 1;
    public const DISPLAY_FULL_NAME             = 'full_name';
    public const DISPLAY_USER_NAME             = 'user_name';
    public const DISPLAY_BOTH                  = 'both';
    public const GENERATED_USER_NAME           = 'user-%s-%s';

    /**
     * @deprecated v5.1.8
     */
    public const DATE_OF_BIRTH_SHOW_DAY_MONTH = 2;

    /**
     * @deprecated v5.1.8
     */
    public const DATE_OF_BIRTH_SHOW_ALL = 4;

    /**
     * Profile/Account setting name.
     */
    public const AUTO_APPROVED_TAGGED_SETTING      = 'user_auto_add_tagger_post';
    public const SORT_FEED_VALUES_SETTING          = 'sort_feed_values';
    public const KEY_SORT_FEED_VALUES_ON_HOME      = 'user.home';
    public const AUTO_PLAY_VIDEO_SETTING           = 'user_auto_play_videos';
    public const SUBSCRIBE_NOTIFICATION_CHANNELS   = 'subscribe_notification_channels';
    public const USER_PROFILE_DATE_OF_BIRTH_FORMAT = 'user_profile_date_of_birth_format';
    public const THEME_TYPE_SETTING                = 'profile_theme_type';
    public const THEME_ID_SETTING                  = 'profile_theme_id';

    public const THEME_PREFERENCE_NAMES = [
        self::THEME_TYPE_SETTING,
        self::THEME_ID_SETTING,
    ];

    public const EXPORT_STATUS_PROCESSING = 'processing';
    public const EXPORT_STATUS_COMPLETED  = 'completed';
    public const EXPORT_STATUS_PENDING    = 'pending';

    private UserRepositoryInterface $repository;

    public function __construct(
        UserRepositoryInterface                   $repository,
        protected UserRelationRepositoryInterface $relationRepository,
        protected FieldRepositoryInterface        $fieldRepository,
        protected SectionRepositoryInterface      $sectionRepository,
    )
    {
        $this->repository = $repository;
    }

    public function isBan(int $userId): bool
    {
        return $this->repository->isBanned($userId);
    }

    public function getFriendship(ContractUser $user, ContractUser $targetUser): ?int
    {
        // todo how to reduce request from users.
        return LoadReduce::get(
            sprintf('friend::friendship(user:%s,owner:%s)', $user->id, $targetUser->id),
            function () use ($user, $targetUser) {
                $friendship = app('events')->dispatch('friend.get_friend_ship', [$user, $targetUser], true);
                if (is_int($friendship)) {
                    return $friendship;
                }

                return $user->entityId() == $targetUser->entityId() ? 5 : 6;
            }
        );
    }

    public function getGuestUser(): Authenticatable
    {
        $user            = new UserModel();
        $user->id        = MetaFoxConstant::GUEST_USER_ID;
        $user->user_name = 'guest';
        $user->full_name = 'Guest';

        try {
            // setup wizard issues auth_roles still not exists.
            $guestUser    = Role::findById(UserRole::GUEST_USER);
            $guestProfile = new UserProfile(['id' => $user->id]);
            $user->setRelation('roles', new Collection([$guestUser]));
            $user->setRelation('profile', $guestProfile);
        } catch (Exception) {
        }

        return $user;
    }

    public function getGender(UserProfile $profile): ?string
    {
        $cacheId = sprintf('gender_phrase_of_%s', $profile->gender_id);

        $phrase = Cache::rememberForever($cacheId, function () use ($profile) {
            if ($profile->gender instanceof UserGender) {
                return $profile->gender->phrase;
            }

            return '_';
        });

        return '_' != $phrase ? __p($phrase) : null;
    }

    public function splitName(string $name): array
    {
        // @todo need to test after.
        $firstName = $middleName = $lastName = '';

        $name = trim($name);

        preg_match('/^([\w-]+)(?:\s+)?(.*?)(?:\s+)?([\w-]+)?$/', $name, $matches);

        if (count($matches)) {
            /*
             * Remove full matching of string
             */
            array_shift($matches);

            $firstName = array_shift($matches);

            $lastName = array_pop($matches);

            if (count($matches)) {
                $middleName = array_shift($matches);
            }
        }

        return [$lastName, $firstName, $middleName];
    }

    public function getLastName(?string $name): string
    {
        if ($name === null) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        [$lastName] = $this->splitName($name);

        return $lastName;
    }

    public function getFirstName(?string $name): string
    {
        if ($name === null) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        [, $firstName, $middleName] = $this->splitName($name);

        if ($middleName && $middleName != '') {
            return $firstName . ' ' . $middleName;
        }

        return $firstName;
    }

    public function getShortName(?string $name): string
    {
        if ($name === null) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $lastName  = self::getLastName($name);
        $firstName = self::getFirstName($name);

        $lastNameString  = ((isset($lastName[0])) ? $lastName[0] : '');
        $firstNameString = ((isset($firstName[0])) ? $firstName[0] : '');

        if (!$lastNameString) {
            return Str::upper($firstNameString . ((isset($firstName[1])) ? $firstName[1] : ''));
        }

        $shortName = $firstNameString . $lastNameString;

        return Str::upper($shortName);
    }

    /**
     * @inherhitDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSummary(ContractUser $context, ?ContractUser $user): ?string
    {
        if ($user == null) {
            return null;
        }

        if (!UserPrivacy::hasAccess($context, $user, 'profile.view_profile')) {
            return null;
        }

        if (!UserPrivacy::hasAccess($context, $user, 'profile.profile_info')) {
            return null;
        }

        $summary = [];
        $profile = $user->profile;

        if (!$profile instanceof UserProfile) {
            return null;
        }
        $address = $this->getAddress($context, $user);
        if ($address) {
            $summary[] = $address;
        }

        $summary[] = Str::limit($profile->about_me, 155);

        return implode('. ', $summary);
    }

    public function getTimeZoneForForm(): array
    {
        $timezones     = [];
        $timezonesData = resolve(TimezoneRepositoryInterface::class)->getTimeZones();

        if ($timezonesData) {
            $timezones = collect($timezonesData[0])->map(function ($value) {
                return [
                    'id'   => $value['id'],
                    'name' => "{$value['name']} ({$value['diff_from_gtm']})",
                ];
            })->toArray();
        }

        return $timezones;
    }

    public function getTimeZoneNameById(int $id): ?string
    {
        if (0 == $id) {
            return null;
        }

        $timezonesData = resolve(TimezoneRepositoryInterface::class)->getTimeZones();

        if ($timezonesData) {
            if (isset($timezonesData[0][$id])) {
                return $timezonesData[0][$id]['name'];
            }
        }

        return null;
    }

    public function getUsersByRoleId(int $roleId): ?CollectionSupport
    {
        return $this->repository->getUsersByRoleId($roleId);
    }

    public function getMentions(string $content): array
    {
        $userIds = [];
        try {
            preg_match_all(self::MENTION_REGEX, $content, $matches);
            $userIds = array_unique($matches[1]);
        } catch (Exception $e) {
            // Silent.
        }

        return $userIds;
    }

    public function getPossessiveGender(?UserGender $gender): string
    {
        $defaultGender = __p('core::phrase.their');

        if (null === $gender) {
            return $defaultGender;
        }

        switch ($gender->entityId()) {
            case MetaFoxConstant::GENDER_MALE:
                $gender = __p('core::phrase.his');
                break;
            case MetaFoxConstant::GENDER_FEMALE:
                $gender = __p('core::phrase.her');
                break;
            default:
                $gender = $defaultGender;
        }

        return $gender;
    }

    public function updateLastLogin(?ContractUser $context): bool
    {
        // check login as guest.
        if (!$context) {
            return false;
        }

        if ($context instanceof HasUserProfile && !$context->isGuest()) {
            return UserActivity::query()
                ->where('id', $context->entityId())
                ->update([
                    'last_login'      => now(),
                    'last_ip_address' => Request::ip(),
                ]);
        }

        return false;
    }

    public function updateLastActivity(ContractUser $context): bool
    {
        if ($context instanceof HasUserProfile && $context->id) {
            self::userStatsActivity($context);

            return UserActivity::query()
                ->where('id', $context->id)
                ->where('last_activity', '<=', Carbon::now()->subMinutes(5))
                ->update([
                    'last_activity' => now(),
                ]);
        }

        return false;
    }

    public function userStatsActivity(ContractUser $context): void
    {
        $now   = Carbon::parse(MetaFox::clientDate());
        $query = UserStatsActivity::query()
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->where('activity_at', '>=', $now->clone()->startOfHour()->utc())
            ->where('activity_at', '<=', $now->clone()->endOfHour()->utc());

        if ($query->exists()) {
            return;
        }

        UserStatsActivity::query()
            ->create([
                'activity_at' => $now->clone()->utc(),
                'user_id'     => $context->entityId(),
                'user_type'   => $context->entityType(),
            ]);

    }

    public function updateInvisibleMode(ContractUser $context, int $isInvisible): UserModel
    {
        return $this->repository->updateUser($context, $context->entityId(), ['is_invisible' => $isInvisible]);
    }

    /**
     * @param ContractUser $user
     *
     * @return array<int, mixed>
     */
    public function getNotificationSettingsByChannel(ContractUser $user, string $channel): array
    {
        $settings = app('events')
            ->dispatch('notification.get_notification_settings_by_channel', [$user, $channel], true);

        return !empty($settings) ? $settings : [];
    }

    /**
     * @param ContractUser      $context
     * @param array<string,int> $attributes
     *
     * @return bool
     * @deprecated
     */
    public function updateNotificationSettingsByChannel(ContractUser $context, array $attributes): bool
    {
        return app_active('metafox/notification')
            && app('events')->dispatch(
                'notification.update_email_notification_settings',
                [$context, $attributes],
                true
            );
    }

    public function hasPendingSubscription(SystemRequest $request, ContractUser $user, bool $isMobile = false): ?array
    {
        return app('events')->dispatch('subscription.check_pending_user', [$request, $user, $isMobile], true);
    }

    /**
     * @inheritDoc
     */
    public function getAddress(ContractUser $context, ContractUser $user): ?string
    {
        if (!$this->canViewLocation($context, $user)) {
            return null;
        }

        if (!$user->profile instanceof UserProfile) {
            return null;
        }

        $profile = $user->profile;
        $country = $state = '';
        $city    = $profile->city_location ?? '';

        if ($profile->country_iso) {
            $country = Country::getCountryName($profile->country_iso);
        }

        if ($country && $profile->country_state_id) {
            $state = Country::getCountryStateName($profile->country_iso, $profile->country_state_id);
        }
        $locations = array_filter([$city, $state, $country]);

        return $locations ? __p('user::phrase.lives_in', ['locations' => implode(', ', $locations)]) : null;
    }

    /**
     * @inheritDoc
     */
    public function isFollowing(ContractUser $context, ContractUser $user): bool
    {
        if (!app('events')->dispatch('follow.is_follow', [$context, $user], true)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function totalFollowers(ContractUser $user): int
    {
        return app('events')->dispatch('follow.get_total_follow', [$user, MetaFoxConstant::VIEW_FOLLOWER], true) ?? 0;
    }

    /**
     * @param ContractUser $user
     *
     * @return array<string, mixed>
     */
    public function getVideoSettings(ContractUser $user): array
    {
        $settings = [
            self::AUTO_PLAY_VIDEO_SETTING,
        ];

        $data = [];

        foreach ($settings as $setting) {
            $data[$setting] = UserValue::getUserValueSettingByName($user, $setting);
        }

        return $data;
    }

    /**
     * @param string|null $birthday
     *
     * @return int|null
     * @deprecated v5.1.8
     */
    public function getUserAge(?string $birthday): ?int
    {
        if (!is_string($birthday)) {
            return null;
        }

        $time = Carbon::createFromFormat('Y-m-d', $birthday);

        if ($time === false) {
            return null;
        }

        return $time->age;
    }

    public function getPointStatistic(ContractUser $user): ?Model
    {
        if (!app_active('metafox/activity')) {
            return null;
        }

        $statistics = app('events')->dispatch('activitypoint.get_point_statistics', [$user], true);

        return $statistics instanceof Model ? $statistics : null;
    }

    /**
     * @inheritDoc
     */
    public function getRoleOptionsForSearchMembers(): array
    {
        $excludeLists = Settings::get('user.user_role_filter_exclude') ?: [];
        $options      = resolve(RoleRepositoryInterface::class)->getRoleOptions();

        if (!empty($excludeLists)) {
            $options = array_values(array_filter($options, function (array $option) use ($excludeLists) {
                if (!isset($option['value'])) {
                    return false;
                }

                if (in_array($option['value'], $excludeLists)) {
                    return false;
                }

                return true;
            }));
        }

        return array_merge(
            [
                [
                    'label' => __p('core::phrase.all'),
                    'value' => null,
                ],
            ],
            $options
        );
    }

    /**
     * @inheritDoc
     */
    public function getAllowApiRules(): array
    {
        $rules = [
            'q'                => ['truthy', 'q'],
            'sort'             => ['includes', 'sort', ['full_name', 'last_login', 'last_activity']],
            'gender'           => ['truthy', 'gender'],
            'view'             => ['includes', 'view', ['recommend', 'featured', 'recent', Browse::VIEW_ALL]],
            'country'          => ['truthy', 'country'],
            'city'             => ['truthy', 'city'],
            'city_code'        => ['truthy', 'city_code'],
            'country_state_id' => ['truthy', 'country_state_id'],
            'is_featured'      => ['truthy', 'is_featured'],
            'group'            => ['truthy', 'group'],
        ];

        $fields = CustomFieldFacade::loadFieldName(Auth::user(), [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        if (empty($fields)) {
            return $rules;
        }

        foreach ($fields as $field) {
            $rules[$field] = ['truthy', $field];
        }

        return $rules;
    }

    public function getActionMetaLogoutOtherDevices(): ActionMeta
    {
        $actionMeta = new ActionMeta();

        $actionMeta->nextAction()
            ->type(MetaFoxConstant::TYPE_REQUEST_ACTION)
            ->payload(
                PayloadActionMeta::payload()
                    ->setAttribute('action', [
                        'module_name'   => 'user',
                        'resource_name' => 'user',
                        'action'        => 'logoutOtherDevices',
                    ])
            );

        return $actionMeta;
    }

    public function getLogoutOptions(): array
    {
        return [
            [
                'value'       => 1,
                'label'       => __p('user::phrase.log_out_of_other_devices'),
                'description' => __p('user::phrase.we_will_help_you_check_for_recent_changes_next'),
            ],
            [
                'value' => 0,
                'label' => __p('user::phrase.stay_logged_in'),
            ],
        ];
    }

    public function getThemeTypeOptions(): array
    {
        return [
            [
                'value' => 'auto',
                'label' => __p('core::web.auto'),
            ],
            [
                'value' => Constants::FORCE_DISPLAY_LIGHT_MODE,
                'label' => __p('core::web.off'),
            ],
            [
                'value' => Constants::FORCE_DISPLAY_DARK_MODE,
                'label' => __p('core::web.on'),
            ],
        ];
    }

    public function getReferenceValueByName(ContractUser $user, string $name): mixed
    {
        if (!$user instanceof UserModel) {
            return null;
        }

        $cacheKey = sprintf(CacheManager::USER_PREFERENCE_VALUE_BY_NAME_CACHE, $name, $user->entityType(), $user->entityId());

        return Cache::rememberForever($cacheKey, function () use ($user, $name) {
            if (!$user->profile instanceof UserProfile) {
                return null;
            }

            $attributeName = sprintf('user_preference_%s', Str::snake($name));

            $value = $user->profile->{$attributeName};

            if (null !== $value) {
                return $value;
            }

            return $user->profile->preferences_value?->get($name);
        });
    }

    public function getReferenceValueByNames(ContractUser $user, array $names): array
    {
        return Arr::mapWithKeys($names, function ($name) use ($user) {
            if (self::getReferenceValueByName($user, $name) == null) {
                return [];
            }

            return [$name => self::getReferenceValueByName($user, $name)];
        });
    }

    public static function allowedStatusExport(): array
    {
        return [
            self::EXPORT_STATUS_PENDING,
            self::EXPORT_STATUS_PROCESSING,
            self::EXPORT_STATUS_COMPLETED,
        ];
    }

    public function allowedPropertiesExport(ContractUser $user): array
    {
        return array_merge(
            $this->getPropertiesBasicInfo(),
            $this->getPropertiesLocalization(),
            $this->getPropertiesMoreInfo(),
            $this->getPropertiesCustomField($user),
        );
    }

    public static function getPropertiesBasicInfo(): array
    {
        return [
            'id'                => __p('core::phrase.id'),
            'user_name'         => __p('core::phrase.user_name'),
            'display_name'      => __p('user::phrase.display_name'),
            'email'             => __p('user::phrase.email'),
            'phone_number'      => __p('core::phrase.phone_number'),
            'role'              => __p('core::phrase.role'),
            'gender'            => __p('user::phrase.gender'),
            'birthday'          => __p('user::phrase.birthday'),
            'age'               => __p('user::phrase.age'),
            'relationship_text' => __p('core::phrase.relationship'),
            'summary'           => __p('profile::phrase.summary_label'),
        ];
    }

    public function getPropertiesCustomField(ContractUser $user): array
    {
        $sectionBasicInfo = $this->sectionRepository->getSectionByName('basic_info');
        $collections      = $this->fieldRepository->getFieldsActiveCollectionByType($user, [
            'section_type' => CustomField::SECTION_TYPE_USER,
        ]);

        if ($sectionBasicInfo) {
            $collections = $collections->where('section_id', '!=', $sectionBasicInfo->entityId());
        }

        $propertiesCustomsFields = [];

        if ($collections->isEmpty()) {
            return $propertiesCustomsFields;
        }

        foreach ($collections as $field) {
            /** @var $field Field */
            $propertiesCustomsFields[$field->field_name] = $field->editingLabel;
        }

        return $propertiesCustomsFields;
    }

    public static function getPropertiesLocalization(): array
    {
        return [
            'language_name' => __p('core::phrase.language'),
            'time_zone'     => __p('core::phrase.time_zone'),
            'location'      => __p('core::phrase.location'),
            'country'       => __p('localize::country.country'),
            'city'          => __p('localize::country.city'),
            'state'         => __p('localize::country.state'),
            'postal_code'   => __p('user::phrase.postal_code'),
            'currency'      => __p('core::phrase.currency'),
            'ip_address'    => __p('user::phrase.ip_address'),
        ];
    }

    public static function getPropertiesMoreInfo(): array
    {
        return [
            'creation_date'       => __p('user::phrase.registration_date'),
            'last_activity'       => __p('user::phrase.last_activity'),
            'last_login'          => __p('user::phrase.last_login'),
            'approval_status'     => __p('user::phrase.approval_status'),
            'verification_status' => __p('user::phrase.verification_status'),
            'avatar_url'          => __p('user::phrase.avatar_url'),
            'cover_url'           => __p('user::phrase.cover_url'),
            'profile_url'         => __p('user::phrase.profile_url'),
        ];
    }

    public static function allowedStatusExportOptions(): array
    {
        return [
            [
                'value' => self::EXPORT_STATUS_PENDING,
                'label' => __p('core::phrase.pending'),
            ],
            [
                'value' => self::EXPORT_STATUS_PROCESSING,
                'label' => __p('core::phrase.processing'),
            ],
            [
                'value' => self::EXPORT_STATUS_COMPLETED,
                'label' => __p('user::phrase.completed'),
            ],
        ];
    }
}
