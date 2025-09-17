<?php

namespace MetaFox\User\Repositories\Eloquent;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Core\Traits\HasValidateUserTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\InvisibleScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Support\Message;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserBan;
use MetaFox\User\Models\UserBlocked;
use MetaFox\User\Models\UserRelationHistory;
use MetaFox\User\Notifications\ProfileUpdatedByAdmin;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Presenters\UserPresenter;
use MetaFox\User\Repositories\CancelFeedbackAdminRepositoryInterface;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\UserPasswordHistoryRepositoryInterface;
use MetaFox\User\Repositories\UserPreferenceRepositoryInterface;
use MetaFox\User\Repositories\UserProfileRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Browse\Scopes\User\CustomFieldScope;
use MetaFox\User\Support\Browse\Scopes\User\RoleScope;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\StatusScope;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\User as SupportUser;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

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
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    use HasFeatured {
        feature as featureByEntityId;
    }
    use CollectTotalItemStatTrait;
    use HasValidateUserTrait;

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
     * Boot up the repository, pushing criteria.
     *
     * @return void
     * @throws RepositoryException
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Include presenter.
     */
    public function presenter(): string
    {
        return UserPresenter::class;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return User
     * @throws Exception
     */
    public function create(array $params): User
    {
        $password = $params['password'] == MetaFoxConstant::EMPTY_STRING
            ? $params['password']
            : bcrypt($params['password']);

        $attributes = [
            'user_name'                => $params['user_name'],
            'full_name'                => Arr::get($params, 'full_name', ''),
            'first_name'               => Arr::get($params, 'first_name', ''),
            'last_name'                => Arr::get($params, 'last_name', ''),
            'email'                    => Arr::get($params, 'email'),
            'phone_number'             => Arr::get($params, 'phone_number'),
            'password'                 => $password,
            'approve_status'           => $params['approve_status'],
            'email_verified_at'        => Arr::get($params, 'email_verified_at'),
            'phone_number_verified_at' => Arr::get($params, 'phone_number_verified_at'),
            'verified_at'              => Arr::get($params, 'verified_at'),
        ];

        $attributes['profile'] = isset($params['profile']) && is_array($params['profile']) ? $params['profile'] : [];

        /** @var User $model */
        $model = $this->getModel()->newModelInstance();
        $model->fill($attributes);
        $model->save();

        $model->refresh();

        if (isset($params['additional_information'])) {
            CustomProfile::saveValues($model, $params['additional_information'], [
                'section_type' => CustomField::SECTION_TYPE_USER,
            ]);
        }

        return $model;
    }

    public function assignRole(int $userId, $roles): User
    {
        $user = $this->find($userId);

        return $user->assignRole($roles);
    }

    public function removeRole(int $userId, $role): User
    {
        $user = $this->find($userId);

        return $user->removeRole($role);
    }

    public function banUser(
        ContractUser $user,
        ContractUser $owner,
                     $day = 0,
                     $returnUserGroup = UserRole::NORMAL_USER_ID,
                     $reason = null
    ): bool
    {
        $ban = $this->getBan($owner->entityId());

        if ($ban == null) {
            $ban                   = new UserBan();
            $ban->start_time_stamp = Carbon::now()->getTimestamp();
        }

        /** @var Role $role */
        $role = Role::findById($returnUserGroup);

        $ban->fill([
            'user_id'           => $user->entityId(),
            'user_type'         => $user->entityType(),
            'owner_id'          => $owner->entityId(),
            'owner_type'        => $owner->entityType(),
            'end_time_stamp'    => $day > 0 ? Carbon::now()->addDays($day)->getTimestamp() : 0,
            'return_user_group' => $role->id,
            'reason'            => $reason,
        ]);

        if ($owner instanceof User) {
            $owner->revokeAllTokens();

            resolve(DeviceRepositoryInterface::class)->logoutAllByUser($owner);
        }

        return $ban->save();
    }

    public function getBan(int $userId): ?UserBan
    {
        /** @var UserBan $banData */
        $banData = UserBan::query()->where('owner_id', $userId)->first();

        if ($banData == null) {
            return null;
        }

        return $banData;
    }

    public function removeBanUser(ContractUser $user, ContractUser $owner): bool
    {
        policy_authorize(UserPolicy::class, 'banUser', $user, $owner);

        if (!$this->isBanned($owner->entityId())) {
            return true;
        }

        $userBan = UserBan::query()->where('owner_id', $owner->entityId())->firstOrFail();

        return (bool) $userBan->delete();
    }

    public function isBanned(int $userId): bool
    {
        $user     = $this->find($userId);
        $isExists = UserBan::query()
            ->where('owner_id', $userId)
            ->where(function ($query) {
                $query->where('end_time_stamp', '=', 0)
                    ->orWhere('end_time_stamp', '>', Carbon::now()->timestamp);
            })
            ->exists();

        if ($isExists) {
            return true;
        }

        return $user->hasRole(UserRole::BANNED_USER_ID);
    }

    public function feature(ContractUser $context, int $id, int $feature): bool
    {
        $resource = $this->with(['profile'])->find($id);

        policy_authorize(UserPolicy::class, 'feature', $context, $resource);

        return $this->featureByEntityId($context, $id, $feature);
    }

    public function cleanUpExpiredBanData(): bool
    {
        $data = UserBan::query()
            ->where('end_time_stamp', '>', 0)
            ->where('end_time_stamp', '<', Carbon::now()->timestamp)
            ->get();

        foreach ($data as $userBan) {
            $userBan->delete();
        }

        return true;
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->getModel()->newModelInstance()
            ->where('email', $this->likeOperator(), $email)
            ->first();
    }

    public function findUserByUserName(string $userName): ?User
    {
        return $this->getModel()->newModelInstance()
            ->where('user_name', '=', $userName)
            ->first();
    }

    public function findUserByEmailOrPhoneNumber(string $value): ?User
    {
        return $this->getModel()->newModelInstance()
            ->where('email', $this->likeOperator(), $value)
            ->orWhere('phone_number', '=', $value)
            ->first();
    }

    public function findAndValidateForAuth(string $username, string $password): ?User
    {
        $user = $this->getModel()->findForPassport($username);

        if (!$user?->validatePassword($password)) {
            return null;
        }

        $verifyBy = $this->getVerifyBy($user, $username);
        if ($verifyBy) {
            $this->validateVerifiedBy($user, $verifyBy);
        }

        $this->validateStatuses($user);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function uploadAvatar(ContractUser $context, ContractUser $owner, array $attributes): array
    {
        $image            = Arr::get($attributes, 'image');
        $imageCrop        = Arr::get($attributes, 'image_crop');
        $photoId          = Arr::get($attributes, 'photo_id');
        $tempFile         = Arr::get($attributes, 'temp_file') ?: 0;
        $isEdittingAvatar = $image === null && $tempFile === 0 && $photoId === null;
        $photos           = null;

        if (!$owner instanceof HasUserProfile) {
            throw new AuthorizationException(null, 403);
        }

        if ($isEdittingAvatar && null == $owner->profile->avatar_file_id) {
            throw ValidationException::withMessages([
                __p('validation.required', ['attribute' => 'image']),
            ]);
        }

        $avatarData = [];
        if ($photoId) {
            $data = app('events')->dispatch('photo.make_profile_avatar', [$owner, $photoId, $imageCrop], true);

            return is_array($data)
                ? $data
                : [
                    'user'    => $owner->refresh(),
                    'feed_id' => 0,
                ];
        }

        if ($image !== null || $tempFile !== 0) {
            $params = [
                'privacy'         => MetaFoxPrivacy::EVERYONE,
                'path'            => 'user',
                'thumbnail_sizes' => ['50x50', '120x120', '200x200'],
                'files'           => [
                    [
                        'file'      => $image,
                        'temp_file' => $tempFile,
                    ],
                ],
            ];

            $photos = $this->createPhoto($context, $owner, $params, 1, User::USER_UPDATE_AVATAR_ENTITY_TYPE);

            $avatarData = $this->getAvatarData($photos);
        }

        $this->handleUploadBase64($owner, $image, $imageCrop, $avatarData);
        LoadReduce::flush();

        $feedId   = 0;
        $itemId   = $owner->profile->avatar_id;
        $itemType = $owner->profile->avatar_type;

        try {
            /** @var Content $feed */
            $feed = app('events')->dispatch(
                'activity.get_feed_by_item_id',
                [$context, $itemId, $itemType, User::USER_UPDATE_AVATAR_ENTITY_TYPE],
                true
            );

            if ($feed instanceof Entity) {
                $feedId = $feed->entityId();

                if (null == $image) {
                    app('events')->dispatch('activity.push_feed_on_top', [$feedId], true);
                }
            }
        } catch (Exception $e) {
            // Silent.
            Log::error($e->getMessage());
        }

        app('events')->dispatch('activitypoint.increase_user_point', [$owner, $owner, 'new_profile_photo']);

        return [
            'user'       => $owner->refresh(),
            'feed_id'    => $feedId,
            'is_pending' => false, //Todo check setting
        ];
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attribute
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function updateAvatar(ContractUser $context, ContractUser $owner, array $attribute): array
    {
        if (!$owner instanceof HasUserProfile) {
            throw new AuthorizationException(null, 403);
        }
        $ownerProfile = $owner->profile;
        $ownerProfile->fill($attribute);
        $ownerProfile->save();

        $feedId = 0;

        try {
            /** @var Content $feed */
            $feed = app('events')->dispatch(
                'activity.get_feed_by_item_id',
                [$context, $ownerProfile->avatar_id, $ownerProfile->avatar_type, User::USER_UPDATE_AVATAR_ENTITY_TYPE],
                true
            );

            if ($feed instanceof Entity) {
                $feed->touch('created_at');

                $feedId = $feed->entityId();

                app('events')->dispatch('activity.push_feed_on_top', [$feedId], true);
            }
        } catch (Exception $e) {
            // Silent.
            Log::error($e->getMessage());
        }

        return [
            'user'    => $owner->refresh(),
            'feed_id' => $feedId,
        ];
    }

    /**
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $params
     * @param int                  $albumType
     * @param string|null          $typeId
     *
     * @return Collection|null
     */
    public function createPhoto(
        ContractUser $context,
        ContractUser $owner,
        array        $params,
        int          $albumType,
        ?string      $typeId = null,
    ): ?Collection
    {
        /** @var Collection $photos */
        $photos = app('events')->dispatch('photo.create', [$context, $owner, $params, $albumType, $typeId], true);

        return $photos;
    }

    /**
     * @param array<string, mixed> $attributes
     * @param int                  $id
     *
     * @return User
     * @throws ValidatorException
     */
    public function update(array $attributes, $id): User
    {
        if (isset($attributes['password'])) {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $model      = $this->find($id);
        $attributes = $this->handleVerify($model, $attributes);

        /* @var User $model */
        $model->update($attributes);

        $profileData = $attributes['profile'] ?? null;

        if (null !== $profileData) {
            $model->loadMissing(['profile']);
            $model->profile->update($profileData);
        }

        $model->refresh();

        if (isset($attributes['password'])) {
            $this->userPasswordHistoryRepository()->create([
                'user_id'   => $model->id,
                'user_type' => $model->entityType(),
                'password'  => $attributes['password'],
            ]);
        }

        return $model;
    }

    protected function handleVerify(User $user, array $params): array
    {
        if ($this->mustVerifyEmail($user->email, $params)) {
            $params['email_verified_at'] = null;

            app('user.verification')->sendVerificationEmail($user, $params['email']);
        }

        if ($this->mustVerifyPhoneNumber($user->phone_number, $params)) {
            $params['phone_number_verified_at'] = null;

            app('user.verification')->sendVerificationPhoneNumber($user, $params['phone_number']);
        }

        return $params;
    }

    public function getVerifyMessage(?string $email, ?string $phoneNumber, array $params): ?string
    {
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.6', '>')) { /* * @deprecated Need remove for some next version  */
            return null;
        }

        $shouldVerifyEmail       = $this->mustVerifyEmail($email, $params);
        $shouldVerifyPhoneNumber = $this->mustVerifyPhoneNumber($phoneNumber, $params);

        if ($shouldVerifyEmail && $shouldVerifyPhoneNumber) {
            return __p('user::phrase.you_have_updated_your_email_and_phone_number');
        }

        if ($shouldVerifyEmail) {
            return __p('user::phrase.you_have_updated_your_email');
        }

        if ($shouldVerifyPhoneNumber) {
            return __p('user::phrase.you_have_updated_your_phone_number');
        }

        return null;
    }

    protected function mustVerifyEmail(?string $email, array $params): bool
    {
        return app('user.verification')->mustVerifyEmail($email, $params);
    }

    protected function mustVerifyPhoneNumber(?string $phoneNumber, array $params): bool
    {
        return app('user.verification')->mustVerifyPhoneNumber($phoneNumber, $params);
    }

    public function updateCover(ContractUser $context, ContractUser $owner, array $attributes): array
    {
        if (!$owner instanceof HasUserProfile) {
            throw new AuthorizationException(null, 403);
        }

        $feedId       = 0;
        $image        = Arr::get($attributes, 'image');
        $tempFile     = Arr::get($attributes, 'temp_file') ?: 0;
        $ownerProfile = $owner->profile;
        $coverData    = [];
        $positionData = [
            'cover_id'             => Arr::get($attributes, 'cover_id', $ownerProfile->cover_id),
            'cover_type'           => 'photo',
            'cover_file_id'        => Arr::get($attributes, 'cover_file_id', $ownerProfile->cover_file_id),
            'cover_photo_position' => Arr::get($attributes, 'position', $ownerProfile->cover_photo_position),
        ];

        if ($image !== null || $tempFile !== 0) {
            $params = [
                'privacy'         => MetaFoxPrivacy::EVERYONE,
                'path'            => 'user',
                'thumbnail_sizes' => $ownerProfile->getCoverSizes(),
                'files'           => [
                    [
                        'file'      => $image,
                        'temp_file' => $tempFile,
                    ],
                ],
            ];

            /** @var Collection $photos */
            $photos = $this->createPhoto($context, $owner, $params, 2, User::USER_UPDATE_COVER_ENTITY_TYPE);

            if (empty($photos)) {
                abort(400, __('validation.something_went_wrong_please_try_again'));
            }

            foreach ($photos as $photo) {
                $photo     = $photo->toArray();
                $coverData = [
                    'cover_id'      => $photo['id'],
                    'cover_type'    => 'photo',
                    'cover_file_id' => $photo['image_file_id'],
                ];

                break;
            }
        }

        $owner->update([
            'profile' => array_merge($positionData, $coverData),
        ]);

        $owner->refresh();

        // $owner->cover;//get photo -> feed
        $itemId   = $owner->profile->cover_id;
        $itemType = $owner->profile->cover_type;

        try {
            /** @var Content $feed */
            $feed = app('events')->dispatch(
                'activity.get_feed_by_item_id',
                [$context, $itemId, $itemType, User::USER_UPDATE_COVER_ENTITY_TYPE],
                true
            );

            if ($feed instanceof Entity) {
                $feed->touch('created_at');

                $feedId = $feed->entityId();

                app('events')->dispatch('activity.push_feed_on_top', [$feedId], true);
            }
        } catch (Exception $e) {
            // Silent.
            Log::error($e->getMessage());
        }

        app('events')->dispatch('activitypoint.increase_user_point', [$owner, $owner, 'new_profile_cover']);

        return [
            'user'       => $owner,
            'feed_id'    => $feedId,
            'is_pending' => false, //Todo check setting
        ];
    }

    public function viewUsers(ContractUser $context, array $attributes): Paginator
    {
        policy_authorize(UserPolicy::class, 'viewAny', $context);

        $limit     = $attributes['limit'];
        $relations = ['profile'];

        $query = $this->buildQueryViewUsers($context, $attributes);

        $query->where('approve_status', MetaFoxConstant::STATUS_APPROVED);
        $query->whereNotNull('verified_at');

        return $query
            ->select(['users.*'])
            ->with($relations)
            ->simplePaginate($limit);
    }

    /**
     * @param ContractUser         $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function buildQueryViewUsers(ContractUser $context, array $attributes): Builder
    {
        $query = $this->getModel()->newModelInstance()->newQuery();

        $sort           = $attributes['sort'];
        $sortType       = $attributes['sort_type'];
        $view           = $attributes['view'];
        $search         = $attributes['q'] ?? '';
        $gender         = $attributes['gender'] ?? null;
        $country        = $attributes['country'] ?? null;
        $city           = $attributes['city'] ?? null;
        $cityCode       = Arr::get($attributes, 'city_code');
        $countryStateId = $attributes['country_state_id'] ?? null;
        $postalCode     = $attributes['postal_code'] ?? null;
        $role           = $attributes['group'] ?? null;
        $email          = $attributes['email'] ?? null;
        $status         = $attributes['status'] ?? null;
        $ageFrom        = $attributes['age_from'] ?? null;
        $ageTo          = $attributes['age_to'] ?? null;
        $ipAddress      = $attributes['ip_address'] ?? null;
        $customFields   = $attributes['custom_fields'] ?? null;
        $isFeatured     = Arr::get($attributes, 'is_featured');

        if (ViewScope::VIEW_RECENT == $view) {
            $sort     = SortScope::SORT_LAST_ACTIVITY;
            $sortType = SortScope::SORT_TYPE_DEFAULT;
        }

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $viewScope = new ViewScope();
        $viewScope->setView($view);

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($context->entityId());

        $query->addScope(new FeaturedScope($isFeatured));
        $query->addScope(new InvisibleScope(0));

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['search_name']));
        }

        if ($gender) {
            $query->whereHas('profile', function (Builder $q) use ($gender) {
                $q->where('gender_id', $gender);
            });
        }

        if ($country) {
            $query->whereHas('profile', function (Builder $q) use ($country) {
                $q->where('country_iso', $country);
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

        if ($ipAddress) {
            $searchScope = new SearchScope($ipAddress, ['user_activities.last_ip_address']);
            $searchScope->setJoinedTable('user_activities');
            $searchScope->setJoinedField('id');
            $query = $query->addScope($searchScope);
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

        $roleScope = new RoleScope();
        $roleScope->setRole($role)
            ->setExcludeRoles(Settings::get('user.user_role_filter_exclude', []));
        $query = $query->addScope($roleScope);

        if ($email) {
            $query = $query->addScope(new SearchScope($email, ['email']));
        }

        if ($status == MetaFoxConstant::STATUS_PENDING_APPROVAL) {
            $query->where('approve_status', MetaFoxConstant::STATUS_PENDING_APPROVAL);
        }

        return $query
            ->addScope($viewScope)
            ->addScope($blockedScope)
            ->addScope($sortScope);
    }

    public function viewUser(ContractUser $context, int $id): User
    {
        $resource = $this->with(['profile'])->find($id);

        policy_authorize(UserPolicy::class, 'view', $context, $resource);

        return $resource;
    }

    public function deleteUser(ContractUser $context, int $id): bool
    {
        $resource = $this->find($id);

        try {
            app('events')->dispatch('user.deleting', [$resource]);

            $resource->delete();

            $resource->revokeAllTokens();

            app('events')->dispatch('user.deleted', [$resource]);
        } catch (\Throwable $error) {
            Log::debug($error->getMessage());
            Log::debug($error->getTraceAsString());
        }

        return true;
    }

    public function removeCover(ContractUser $context, int $id): bool
    {
        $resource = $this->with(['profile'])->find($id);

        policy_authorize(UserPolicy::class, 'update', $context, $resource);

        return $resource->update([
            'profile' => $resource->profile->getCoverDataEmpty(),
        ]);
    }

    /**
     * @param ContractUser $context
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function getInvisibleSettings(ContractUser $context): array
    {
        policy_authorize(UserPolicy::class, 'viewAny', $context);

        return [
            'module_id'   => $context->entityType(),
            'phrase'      => __p('user::phrase.enable_invisible_mode'),
            'description' => __p('user::phrase.enable_invisible_mode_description'),
            'var_name'    => 'invisible',
            'value'       => $context->is_invisible ?? 0,
        ];
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function updateUser(ContractUser $context, int $id, array $attributes): User
    {
        $user = $this->with(['profile'])->find($id);

        resolve(UserProfileRepositoryInterface::class)->checkUpdatePermission($context, $user, $attributes);

        $this->handleProfileFeed($user, $attributes);

        $this->handleRelationshipFeed($user, $attributes);

        if (isset($attributes['additional_information'])) {
            CustomProfile::saveValues($user, $attributes['additional_information'], [
                'section_type' => CustomField::SECTION_TYPE_USER,
            ]);
        }

        if (isset($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $attributes = $this->handleVerify($user, $attributes);

        $user->fill($attributes);

        $user->save();

        if ($user->entityId() != $context->entityId()) {
            Notification::send($user, new ProfileUpdatedByAdmin($user));
        }

        $user->refresh();

        return $user;
    }

    protected function handleProfileFeed(ContractUser $user, array $attributes): void
    {
        if (!app_active('metafox/activity')) {
            return;
        }

        if (!Settings::get('user.enable_feed_user_update_profile', false)) {
            return;
        }

        $newProfile = Arr::get($attributes, 'profile');

        if (!is_array($newProfile)) {
            return;
        }

        if (null === $user->profile) {
            return;
        }

        $oldProfile = $user->profile->toArray();

        if (!$this->isProfileChanged($newProfile, $oldProfile)) {
            return;
        }

        $this->pushProfileFeed($user);
    }

    public function pushProfileFeed(ContractUser $user): void
    {
        /** @var Content $feed */
        $feed = app('events')->dispatch(
            'activity.get_feed_by_item_id',
            [$user, $user->entityId(), $user->entityType(), User::USER_UPDATE_INFORMATION_ENTITY_TYPE, false],
            true
        );

        $alreadyExists = null !== $feed;

        if (!$alreadyExists) {
            $feed = app('events')->dispatch(
                'activity.create_feed',
                [$this->getProfileFeedAction($user)],
                true
            );
        }

        if (null === $feed) {
            return;
        }

        if ($alreadyExists) {
            $feed->touch('created_at');
        }

        $feedId = $feed->entityId();

        app('events')->dispatch('activity.push_feed_on_top', [$feedId], true);
    }

    protected function getProfileFeedAction(ContractUser $user): ?FeedAction
    {
        return new FeedAction([
            'user_id'    => $user->userId(),
            'user_type'  => $user->userType(),
            'owner_id'   => $user->ownerId(),
            'owner_type' => $user->ownerType(),
            'item_id'    => $user->entityId(),
            'item_type'  => $user->entityType(),
            'type_id'    => User::USER_UPDATE_INFORMATION_ENTITY_TYPE,
            'privacy'    => MetaFoxPrivacy::EVERYONE,
        ]);
    }

    public function getChangeableProfileFields(): array
    {
        return [
            'country_iso', 'country_state_id', 'country_city_code',
            'address', 'postal_code', 'gender_id', 'birthday',
        ];
    }

    protected function isProfileChanged(array $newProfile, array $oldProfile): bool
    {
        $attributes = $this->getChangeableProfileFields();

        foreach ($attributes as $attribute) {
            if (Arr::get($oldProfile, $attribute) != Arr::get($newProfile, $attribute)) {
                return true;
            }
        }

        return false;
    }

    protected function handleRelationshipFeed(ContractUser $context, array $attributes): void
    {
        if (!app_active('metafox/activity')) {
            return;
        }

        if (!Settings::get('user.enable_feed_user_update_relationship', true)) {
            return;
        }

        $newProfile = Arr::get($attributes, 'profile', []);

        if (!count($newProfile)) {
            return;
        }

        $profile = $context->profile;

        if (null === $profile) {
            return;
        }

        if (!$this->isRelationshipChanged($newProfile, $profile->toArray())) {
            return;
        }

        UserRelationHistory::query()->create([
            'user_id'       => $context->entityId(),
            'user_type'     => $context->entityType(),
            'relation_id'   => Arr::get($newProfile, 'relation'),
            'relation_with' => Arr::get($newProfile, 'relation_with', 0),
        ]);
    }

    /**
     * isRelationshipChanged.
     *
     * @param array<mixed> $newProfile
     * @param array<mixed> $oldProfile
     *
     * @return bool
     */
    protected function isRelationshipChanged(array $newProfile, array $oldProfile): bool
    {
        if (!Arr::get($newProfile, 'relation')) {
            return false;
        }

        return Arr::get($oldProfile, 'relation') != Arr::get($newProfile, 'relation');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function createUser(array $attributes): ?User
    {
        $code       = Arr::get($attributes, 'code');
        $inviteCode = Arr::get($attributes, 'invite_code');

        $user = $this->create($attributes);

        $settingRoleId = Settings::get('user.on_register_user_group', UserRole::NORMAL_USER);

        $user->assignRole($settingRoleId);

        LoadReduce::flush();

        app('events')->dispatch('user.registration.extra_field.create', [$user, $attributes]);

        // Update user activity point when sign up
        app('events')->dispatch('activitypoint.increase_user_point', [$user, $user, 'sign_up']);

        if (!$inviteCode && !$code) {
            return $user;
        }

        app('events')->dispatch('invite.user_register_update_status', [$user, $code, $attributes], true);

        return $user->refresh();
    }

    public function searchBlockUser(ContractUser $user, string $search)
    {
        return UserBlocked::query()
            ->join('user_entities', 'user_entities.id', '=', 'user_blocked.owner_id')
            ->where('user_blocked.user_id', $user->entityId())
            ->where('user_entities.name', $this->likeOperator(), '%' . $search . '%')
            ->get(['owner_id', 'user_id'])
            ->pluck('user_id', 'owner_id')
            ->toArray();
    }

    protected function getUsersByLocation(User $context): Collection
    {
        $country = $context->profile->country_state_id;
        $city    = $context->profile->city_location;
        if ($city == null) {
            return collect();
        }

        return $this->getModel()->newQuery()->with('profile')
            ->leftJoin('user_profiles as profile', 'profile.id', '=', 'users.id')
            ->where('profile.country_state_id', $country)
            ->where('profile.city_location', $city)
            ->whereNot('users.id', $context->entityId())
            ->get();
    }

    /**
     * @param ContractUser         $context
     * @param UploadedFile         $image
     * @param array<string, mixed> $params
     *
     * @return void
     * @throws AuthorizationException
     */
    public function createAvatarFromSignup(ContractUser $context, UploadedFile $image, array $params): void
    {
        if (!$context instanceof HasUserProfile) {
            throw new AuthorizationException(null, 403);
        }

        $imageCrop = Arr::get($params, 'imageCrop', null);
        $data      = [
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'files'   => [
                [
                    'file' => $image,
                    'path' => 'user',
                ],
            ],
        ];

        $photos     = $this->createPhoto($context, $context, $data, 1, User::USER_AVATAR_SIGN_UP);
        $avatarData = $this->getAvatarData($photos);

        $this->handleUploadBase64($context, $image, $imageCrop, $avatarData);
        LoadReduce::flush();
    }

    public function getAdminAndStaffOptions(): array
    {
        $query = $this->getModel()->newModelInstance()->newQuery();
        $query->where('users.approve_status', MetaFoxConstant::STATUS_APPROVED);

        $roles = [
            UserRole::SUPER_ADMIN_USER_ID,
            UserRole::ADMIN_USER_ID,
            UserRole::STAFF_USER_ID,
        ];

        $roleScope = new RoleScope();
        $roleScope->setRoles($roles);

        $users = $query->addScope($roleScope)->get()->collect();

        return $users->map(function (User $user) {
            return [
                'value' => $user->entityId(),
                'label' => $user->display_name,
            ];
        })->values()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getItemExtraStatistics(ContractUser $context, User $user, string $itemType, int $itemId): array
    {
        $statistics = app('events')->dispatch('user.extra_statistics', [$context, $user, $itemType, $itemId], true);

        if (null === $statistics) {
            return [];
        }

        return $statistics;
    }

    public function getOnlineUserForStat(?Carbon $after = null, ?Carbon $before = null): int
    {
        $to = $after instanceof Carbon
            ? $after->clone()
            : Carbon::now()->subMinutes(5);

        $from = $before instanceof Carbon
            ? $before->clone()
            : Carbon::now()->endOfHour();

        $query = $this->getModel()->newModelQuery()
            ->join('user_stats_activities', 'user_stats_activities.user_id', '=', 'users.id');

        $query->where('user_stats_activities.activity_at', '>=', $to);

        if ($from) {
            $query->where('user_stats_activities.activity_at', '<=', $from);
        }

        $data = $query->get();

        if ($to->diffInHours($from) <= 1) {
            return $data->count();
        }

        return $data->groupBy('user_id')->count();
    }

    public function getOnlineUserCount(?Carbon $after = null, ?Carbon $before = null): int
    {
        $to = $after instanceof Carbon
            ? $after->clone()
            : Carbon::now()->subMinutes(5);

        $query = $this->getModel()->newModelQuery()->join('user_activities', 'user_activities.id', '=', 'users.id');

        $query->where('user_activities.last_activity', '>=', $to);

        if ($before) {
            $query->where('user_activities.last_activity', '<=', $before);
        }

        return $query->count();
    }

    public function getPendingUserCount(): int
    {
        $statusScope = new StatusScope();
        $statusScope->setStatus(MetaFoxConstant::STATUS_PENDING_APPROVAL);

        return $this->getModel()
            ->newModelQuery()
            ->addScope($statusScope)
            ->count();
    }

    public function getUserByRoleId(int $roleId): User
    {
        $query = $this->getModel()->newModelInstance()->newQuery();

        $roleScope = new RoleScope();
        $roleScope->setRole($roleId);
        $query = $query->addScope($roleScope);

        return $query->first();
    }

    /**
     * @inheritDoc
     */
    public function getUsersByRoleId(int $roleId): ?Collection
    {
        $query = $this->getModel()->newModelInstance()->newQuery();

        $roleScope = new RoleScope();
        $roleScope->setRole($roleId);
        $query = $query->addScope($roleScope);

        return $query->get();
    }

    public function getSuperAdmin(): ?User
    {
        $hasRoleTable = config('permission.table_names.model_has_roles');

        return User::query()
            ->join($hasRoleTable . ' as has_role', function (JoinClause $joinClause) {
                $joinClause->on('has_role.model_id', '=', 'users.id')
                    ->where([
                        'has_role.model_type' => User::ENTITY_TYPE,
                        'has_role.role_id'    => UserRole::SUPER_ADMIN_USER_ID,
                    ]);
            })
            ->orderBy('users.id')
            ->first();
    }

    /**
     * @param ContractUser         $context
     * @param int                  $id
     * @param array<string, mixed> $params
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function cancelAccount(ContractUser $context, int $id, array $params): bool
    {
        $user        = $this->find($id);
        $reasonId    = Arr::get($params, 'reason_id', 0);
        $feedback    = Arr::get($params, 'feedback', '');
        $phoneNumber = $user->phone_number;

        policy_authorize(UserPolicy::class, 'delete', $context, $user);

        $this->deleteUser($context, $id);

        resolve(CancelFeedbackAdminRepositoryInterface::class)->createFeedback($context, [
            'email'         => $user->email ?? '',
            'name'          => $user->display_name ?? 'Unknown',
            'user_id'       => $user->entityId(),
            'user_group_id' => $user->roleId(),
            'user_type'     => $user->entityType(),
            'reason_id'     => $reasonId,
            'feedback_text' => $this->cleanTitle($feedback),
            'phone_number'  => $phoneNumber,
        ]);

        return true;
    }

    public function cleanUpDeletedUser(int $period = 1): void
    {
        $deleteTime = Carbon::now()->subDays($period);

        $this->getModel()
            ->newModelQuery()
            ->where('deleted_at', '<=', $deleteTime)
            ->get()
            ->collect()
            ->each(function (User $deletedUser) {
                UserEntity::forceDeleteEntity($deletedUser->entityId());
                $deletedUser->forceDelete();
            });
    }

    /**
     * @throws AuthorizationException
     */
    public function approve(ContractUser $context, int $id): Content
    {
        $resource = $this->find($id);

        policy_authorize(UserPolicy::class, 'approve', $context);

        $success = $resource->update(['approve_status' => MetaFoxConstant::STATUS_APPROVED]);

        if ($success) {
            app('events')->dispatch('models.notify.approved', [$context, $resource], true);
            app('events')->dispatch('sticker.add_default_sticker_for_user', [$resource], true);
            app('events')->dispatch('invite.user_register_update_status', [$resource], true);
            app('events')->dispatch('user.signup_new_friend', [$resource]);
        }

        return $resource->refresh();
    }

    /**
     * @param ContractUser $context
     * @param int          $id
     * @param array        $attributes
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function denyUser(ContractUser $context, int $id, array $attributes): Content
    {
        $resource = $this->find($id);

        policy_authorize(UserPolicy::class, 'approve', $context);

        $success = $resource->update(['approve_status' => MetaFoxConstant::STATUS_NOT_APPROVED]);

        if ($success) {
            $this->handleSendingEmail($resource, $attributes);
            $this->handleSendingSms($resource, $attributes);
        }

        return $resource->refresh();
    }

    protected function handleSendingSms(User $user, array $attributes): void
    {
        $isSentSms = Arr::get($attributes, 'has_send_sms', false);

        if (!$isSentSms) {
            return;
        }

        $fullName    = $user->full_name;
        $email       = $user->getEmailForVerification();
        $phoneNumber = $user->getPhoneNumberForVerification();
        $smsMessage  = Arr::get($attributes, 'sms_message');
        $content     = __p('user::mail.deny_email_sms', [
            'full_name'    => $fullName,
            'email'        => $email,
            'message'      => $smsMessage,
            'phone_number' => $phoneNumber,
        ]);

        /** @var Message $message */
        $message = resolve(Message::class);
        $message->setContent($content);
        $message->setRecipients($phoneNumber);
        $message->setUrl(null);

        /** @var ManagerInterface $manager */
        $manager = resolve(ManagerInterface::class);
        $manager->service()->send($message);
    }

    protected function handleSendingEmail(User $user, array $attributes): void
    {
        $isSentMail = Arr::get($attributes, 'has_send_mail', false);

        if (!$isSentMail) {
            return;
        }

        $fullName = $user->full_name;
        $email    = $user->getEmailForVerification();
        $subject  = Arr::get($attributes, 'subject');
        $message  = Arr::get($attributes, 'message');

        Mail::to($email)
            ->send(new Mailable([
                'subject' => $subject,
                'line'    => __p('user::mail.deny_email_html', [
                    'full_name' => $fullName,
                    'email'     => $email,
                    'message'   => $message,
                ]),
                'user'    => $user,
            ]));
    }

    /**
     * @inheritDoc
     */
    public function updateVideosSettings(ContractUser $context, ContractUser $user, array $attributes): ContractUser
    {
        $settings = UserFacade::getVideoSettings($user);
        $keys     = array_keys($settings);

        $data = Arr::only($attributes, $keys);

        UserValue::updateUserValueSetting($user, $data);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function updateThemePreference(ContractUser $user, array $attributes = []): ContractUser
    {
        $attributes = Arr::only($attributes, SupportUser::THEME_PREFERENCE_NAMES);

        resolve(UserPreferenceRepositoryInterface::class)->updateOrCreatePreferences($user, $attributes);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function updatePreference(ContractUser $user, array $attributes = []): ContractUser
    {
        $attributes = Arr::only($attributes, SupportUser::SUBSCRIBE_NOTIFICATION_CHANNELS);

        resolve(UserPreferenceRepositoryInterface::class)->updateOrCreatePreferences($user, $attributes);

        return $user;
    }

    public function getAvatarData(?Collection $photos): array
    {
        $avatarData = [];
        if (empty($photos)) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        foreach ($photos as $photo) {
            $photo->toArray();

            $avatarData = [
                'avatar_id'      => $photo['id'],
                'avatar_type'    => 'photo',
                'avatar_file_id' => $photo['image_file_id'],
            ];

            break;
        }

        return $avatarData;
    }

    /**
     * @param ContractUser      $context
     * @param UploadedFile|null $image
     * @param string|null       $imageCrop
     * @param array             $avatarData
     *
     * @return void
     * @throws AuthorizationException
     */
    public function handleUploadBase64(ContractUser $context, ?UploadedFile $image, ?string $imageCrop, array $avatarData): void
    {
        $uploadedFile = is_string($imageCrop) ? upload()->convertBase64ToUploadedFile($imageCrop) : $image;

        if (!$context instanceof HasUserProfile) {
            throw new AuthorizationException(null, 403);
        }

        $storageFile = upload()
            ->setThumbSizes(['50x50', '120x120', '200x200'])
            ->setPath('user')
            ->setStorage('photo')
            ->storeFile($uploadedFile);

        Arr::set($avatarData, 'avatar_file_id', $storageFile?->entityId());
        if (count($avatarData)) {
            $context->profile->update($avatarData);
        }

        $context->profile->refresh();
    }

    /**
     * @inheritDoc
     */
    public function getAllSuperAdmin(): Collection
    {
        return $this->getUsersByRoleId(UserRole::SUPER_ADMIN_USER_ID);
    }

    private function userPasswordHistoryRepository(): UserPasswordHistoryRepositoryInterface
    {
        return resolve(UserPasswordHistoryRepositoryInterface::class);
    }
}
