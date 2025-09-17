<?php

namespace MetaFox\User\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User as ContractsUser;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Jobs\LogoutAllUsersJob;
use MetaFox\User\Jobs\MassSendInactiveMailingJob;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Notifications\ProcessMailingInactiveUser;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\InactiveProcessAdminRepositoryInterface;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\CustomFieldScope;
use MetaFox\User\Support\Browse\Scopes\User\RoleScope;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\StatusScope;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User as UserSupport;

/**
 * Class UserRepositoryRepository.
 *
 * @property User $model
 * @method   User getModel()
 * @method   User find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class UserAdminRepository extends AbstractRepository implements UserAdminRepositoryInterface
{
    protected function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    protected function inactiveProcessRepository(): InactiveProcessAdminRepositoryInterface
    {
        return resolve(InactiveProcessAdminRepositoryInterface::class);
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return User::class;
    }

    /**
     * @param ContractsUser $context
     * @param int           $id
     * @param array         $attributes
     *
     * @return User
     * @throws AuthorizationException
     */
    public function updateUser(ContractsUser $context, int $id, array $attributes): User
    {
        $user   = $this->with(['profile'])->find($id);
        $roleId = Arr::get($attributes, 'role_id');

        policy_authorize(UserPolicy::class, 'manage', $context, $user);

        if (Arr::has($attributes, 'password')) {
            Arr::set($attributes, 'password', bcrypt(Arr::get($attributes, 'password')));
        }

        if (Arr::has($attributes, 'additional_information')) {
            $this->updateUserCustomField($user, Arr::get($attributes, 'additional_information'));
        }

        if (Arr::has($attributes, 'privacy')) {
            $this->updateUserPrivacy($user, Arr::get($attributes, 'privacy'));
        }

        if (Arr::has($attributes, 'notification')) {
            $this->updateUserNotification($user, Arr::get($attributes, 'notification'));
        }

        if (Arr::has($attributes, 'avatar')) {
            $this->updateUserAvatar($user, Arr::get($attributes, 'avatar'));
        }

        $user->fill($attributes);
        $user->save();

        if (isset($roleId) && $roleId != $user->roleId()) {
            $user->syncRoles($roleId);

            app('events')->dispatch('user.role.downgrade', [$context, $user]);
        }

        $user->refresh();

        return $user;
    }

    public function updateUserAvatar(ContractsUser $user, array $image): void
    {
        $profile = $user->profile;
        if (!$profile instanceof UserProfile) {
            return;
        }

        if (Arr::get($image, 'is_delete', false)) {
            $user->update(['profile' => $profile->getAvatarDataEmpty()]);

            return;
        }

        if (!Arr::has($image, 'base64')) {
            return;
        }

        $imageCrop = Arr::get($image, 'base64');
        $image     = $imageCrop ? upload()->convertBase64ToUploadedFile($imageCrop) : null;
        if (!$image instanceof UploadedFile) {
            return;
        }

        $data = [
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'files'   => [
                [
                    'file' => $image,
                    'path' => 'user',
                ],
            ],
        ];

        $photos     = $this->userRepository()->createPhoto($user, $user, $data, 1, User::USER_UPDATE_AVATAR_ENTITY_TYPE);
        $avatarData = $this->userRepository()->getAvatarData($photos);

        $this->userRepository()->handleUploadBase64($user, $image, $imageCrop, $avatarData);
    }

    private function updateUserNotification(ContractsUser $user, array $notifications): void
    {
        foreach ($notifications as $notification) {
            UserFacade::updateNotificationSettingsByChannel($user, $notification);
        }
    }

    private function updateUserCustomField(ContractsUser $user, array $profile): void
    {
        CustomProfile::saveValues($user, $profile, [
            'section_type' => CustomField::SECTION_TYPE_USER,
        ]);
    }

    private function updateUserPrivacy(ContractsUser $user, array $privacy): void
    {
        $userId = $user->entityId();

        $this->updateOtherUserPrivacy($user, $privacy);

        UserPrivacy::validateProfileSettings($userId, $privacy);

        resolve(UserPrivacyRepositoryInterface::class)
            ->updateUserPrivacy($userId, $privacy);
    }

    private function updateOtherUserPrivacy(ContractsUser $user, array &$privacy): void
    {
        $userEntity = UserEntity::getById($user->entityId())->detail;

        $otherPrivacies = [
            UserSupport::USER_PROFILE_DATE_OF_BIRTH_FORMAT,
            UserSupport::AUTO_APPROVED_TAGGED_SETTING,
        ];

        foreach ($otherPrivacies as $otherPrivacy) {
            if (!Arr::has($privacy, $otherPrivacy)) {
                continue;
            }

            UserValue::updateUserValueSetting(
                $userEntity,
                [$otherPrivacy => $privacy[$otherPrivacy]]
            );
        }

        Arr::forget($privacy, $otherPrivacies);
    }

    public function moveRole(ContractsUser $context, ContractsUser $user, int $roleId): bool
    {
        policy_authorize(UserPolicy::class, 'manage', $context, $user);

        if (!isset($roleId)) {
            return false;
        }

        if ($roleId == $user->roleId()) {
            return false;
        }

        $user->syncRoles($roleId);

        app('events')->dispatch('user.role.downgrade', [user(), $user]);

        return $user instanceof User;
    }

    public function verifyUser(ContractsUser $context, User $user): bool
    {
        policy_authorize(UserPolicy::class, 'manage', $context, $user);

        if ($user->shouldVerifyPhoneNumber()) {
            $user->markPhoneNumberAsVerified();
        }

        if ($user->shouldVerifyEmailAddress()) {
            $user->markEmailAsVerified();
        }

        $user->markAsVerified();

        return true;
    }

    /**
     * @throws AuthorizationException
     */
    public function viewUsers(ContractsUser $context, array $attributes): LengthAwarePaginator
    {
        policy_authorize(UserPolicy::class, 'viewAdminCP', $context);

        $limit = $attributes['limit'];

        $relations = ['profile'];

        $query = $this->buildQueryViewUsers($attributes);

        return $query
            ->select(['users.*'])
            ->with($relations)
            ->paginate($limit);
    }

    public function buildQueryViewUsers(array $attributes): Builder
    {
        $query = $this->getModel()->newModelInstance()->newQuery();

        $sort           = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType       = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $view           = Arr::get($attributes, 'view', ViewScope::VIEW_DEFAULT);
        $search         = Arr::get($attributes, 'q');
        $gender         = Arr::get($attributes, 'gender');
        $day            = Arr::get($attributes, 'day');
        $country        = Arr::get($attributes, 'country');
        $city           = Arr::get($attributes, 'city');
        $cityCode       = Arr::get($attributes, 'city_code');
        $countryStateId = Arr::get($attributes, 'country_state_id');
        $postalCode     = Arr::get($attributes, 'postal_code');
        $role           = Arr::get($attributes, 'group');
        $email          = Arr::get($attributes, 'email');
        $status         = Arr::get($attributes, 'status');
        $ageFrom        = Arr::get($attributes, 'age_from');
        $ageTo          = Arr::get($attributes, 'age_to');
        $ipAddress      = Arr::get($attributes, 'ip_address');
        $customFields   = Arr::get($attributes, 'custom_fields');
        $phoneNumber    = Arr::get($attributes, 'phone_number');
        $currencyId     = Arr::get($attributes, 'currency_id');

        if (ViewScope::VIEW_RECENT == $view) {
            $sort = SortScope::SORT_LAST_ACTIVITY;
        }

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $viewScope = new ViewScope();
        $viewScope->setView($view);

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['full_name', 'user_name']));
        }

        if (!empty($gender)) {
            $query->whereHas('profile', function (Builder $q) use ($gender) {
                $q->whereIn('gender_id', Arr::wrap($gender));
            });
        }

        if (!empty($country)) {
            $query->whereHas('profile', function (Builder $q) use ($country) {
                $q->whereIn('country_iso', Arr::wrap($country));
            });
        }

        if ($cityCode) {
            $query->whereHas('profile', function (Builder $q) use ($cityCode) {
                $q->where('country_city_code', $cityCode);
            });
        }

        if ($countryStateId) {
            $query->whereHas('profile', function (Builder $q) use ($countryStateId) {
                $q->where('country_state_id', $countryStateId);
            });
        }

        if ($city) {
            $query->whereHas('profile', function (Builder $q) use ($city) {
                $q->where('city_location', $city);
            });
        }

        if ($currencyId) {
            $query->whereHas('profile', function (Builder $q) use ($currencyId) {
                app('currency')->getDefaultCurrencyId() == $currencyId
                    ? $q->whereNull('currency_id')->orWhere('currency_id', $currencyId)
                    : $q->where('currency_id', $currencyId);
            });
        }

        if ($postalCode) {
            $query->whereHas('profile', function (Builder $q) use ($postalCode) {
                $q->where('postal_code', $postalCode);
            });
        }

        if ($ageFrom) {
            $query->whereHas('profile', function (Builder $q) use ($ageFrom) {
                $q->whereYear('birthday', '<=', $ageFrom);
            });
        }

        if ($ageTo) {
            $query->whereHas('profile', function (Builder $q) use ($ageTo) {
                $q->whereYear('birthday', '>=', $ageTo);
            });
        }

        if ($phoneNumber) {
            $query = $query->addScope(new SearchScope($phoneNumber, ['phone_number']));
        }

        if ($ipAddress) {
            $searchScope = new SearchScope($ipAddress, ['uac.last_ip_address']);
            $searchScope->setJoinedTable('user_activities');
            $searchScope->setAliasJoinedTable('uac');
            $searchScope->setJoinedField('id');
            $query = $query->addScope($searchScope);
        }

        if ($day) {
            $previousDay = Carbon::now()->subDays($day)->endOfDay()->toDateTimeString();

            $query->whereHas('userActivity', function (Builder $q) use ($previousDay) {
                $q->whereDate('last_login', '<=', $previousDay);
            });
        }

        if ($status) {
            $statusScope = new StatusScope();
            $statusScope->setStatus($status);

            $query = $query->addScope($statusScope);
        }

        if ($customFields) {
            $customFieldScope = new CustomFieldScope();
            $customFieldScope->setCustomFields($customFields);
            $customFieldScope->setCurrentTable($this->getModel()->getTable());
            $customFieldScope->setSectionType(CustomField::SECTION_TYPE_USER);

            $query = $query->addScope($customFieldScope);
        }

        if (!empty($role)) {
            $roleScope = new RoleScope();
            $roleScope->setRoles(Arr::wrap($role));
            $query = $query->addScope($roleScope);
        }

        if ($email) {
            $query = $query->addScope(new SearchScope($email, ['email']));
        }

        if ($status == MetaFoxConstant::STATUS_PENDING_APPROVAL) {
            $query->where('approve_status', MetaFoxConstant::STATUS_PENDING_APPROVAL);
        }

        return $query
            ->addScope($viewScope)
            ->addScope($sortScope);
    }

    public function processMailing(ContractsUser $context, ContractsUser $user): bool
    {
        if (!$this->checkPermissionProcessMailing($context, $user)) {
            return false;
        }

        Notification::send($user, new ProcessMailingInactiveUser($user));

        return true;
    }

    public function batchProcessMailing(ContractsUser $context, array $userIds = []): void
    {
        $collections = array_chunk($userIds, 5);

        foreach ($collections as $collection) {
            MassSendInactiveMailingJob::dispatch($context, $collection);
        }
    }

    public function processMailingAll(ContractsUser $context, array $attributes): void
    {
        $users       = $this->buildQueryViewUsers($attributes)->get();
        $collections = $users->chunk(5);

        foreach ($collections as $collection) {
            MassSendInactiveMailingJob::dispatch($context, $collection->pluck('id')->toArray());
        }
    }

    protected function checkPermissionProcessMailing(ContractsUser $context, ContractsUser $user): bool
    {
        if (!policy_check(UserPolicy::class, 'manage', $context, $user)) {
            return false;
        }

        if ($context->entityId() == $user->entityId()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function logoutAllUsers(ContractsUser $context, array $attributes): void
    {
        $optionsWithout = $context->hasSuperAdminRole()
            ? [UserRole::SUPER_ADMIN_USER]
            : [UserRole::SUPER_ADMIN_USER, UserRole::ADMIN_USER];

        $roles = resolve(RoleRepositoryInterface::class)->getRoleOptionsWithout($optionsWithout);
        $query = $this->getModel()->newModelInstance()->newQuery();
        $roles = Arr::pluck($roles, 'value');

        if (!empty($roles)) {
            $roleScope = new RoleScope();
            $roleScope->setRoles(Arr::wrap($roles));
            $query = $query->addScope($roleScope);
        }

        $users       = $query->get();
        $collections = $users->chunk(100);

        foreach ($collections as $collection) {
            LogoutAllUsersJob::dispatch($collection->pluck('id')->toArray());
        }
    }
}
