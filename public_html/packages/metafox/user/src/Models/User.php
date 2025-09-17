<?php

namespace MetaFox\User\Models;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;
use Laravel\Passport\RefreshTokenRepository;
use MetaFox\Authorization\Traits\HasRoles;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasTimelineAlbum;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\PrivacyList;
use MetaFox\Platform\Contracts\ResourcePostOnOwner;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasUser;
use MetaFox\Platform\Traits\Eloquent\Model\HasFollowTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Notifiable;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Contracts\CanResetPassword;
use MetaFox\User\Contracts\UserHasValuePermission;
use MetaFox\User\Database\Factories\UserFactory;
use MetaFox\User\Exceptions\ValidateUserException;
use MetaFox\User\Notifications\UserApproveNotification;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\Eloquent\UserRepository;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\User as FacadesUser;
use MetaFox\User\Traits\CanResetPasswordTrait;
use MetaFox\User\Traits\MustVerify;
use MetaFox\User\Traits\UserHasValuePermissionTrait;

/**
 * Class User.
 *
 * @property int          $id
 * @property string       $user_name
 * @property string       $full_name
 * @property string       $display_name
 * @property string       $full_name_raw
 * @property string|null  $first_name
 * @property string|null  $last_name
 * @property string       $email
 * @property string       $phone_number
 * @property string       $password
 * @property string       $created_at
 * @property string       $updated_at
 * @property int          $total_friend
 * @property int          $total_follower,
 * @property int          $total_following
 * @property UserProfile  $profile
 * @property UserActivity $userActivity
 * @property bool         $is_featured
 * @property ?mixed       $email_verified_at
 * @property ?mixed       $phone_number_verified_at
 * @property ?mixed       $verified_at
 * @property bool         $is_invisible
 * @property int          $is_approved
 * @property string       $approve_status
 * @mixin Builder
 * @method static UserFactory factory(...$parameters)
 * @method        int         entityId()
 * @method        string      entityType()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class User extends Authenticatable implements
    ContractUser,
    PrivacyList,
    IsActivitySubscriptionInterface,
    ActivityFeedSource,
    IsPrivacyItemInterface,
    PostBy,
    IsNotifiable,
    HasUserProfile,
    UserHasValuePermission,
    CanResetPassword,
    HasGlobalSearch,
    HasTimelineAlbum,
    HasApprove,
    HasFeature
{
    use Notifiable;
    use HasApiTokens;
    use HasRoles;
    use HasNestedAttributes;
    use HasFactory;
    use UserHasValuePermissionTrait;
    use CanResetPasswordTrait;
    use HasUser;
    use MustVerify;
    use HasFollowTrait;

    public const ENTITY_TYPE = 'user';

    public const    USER_UPDATE_AVATAR_ENTITY_TYPE       = 'user_update_avatar';
    public const    USER_UPDATE_COVER_ENTITY_TYPE        = 'user_update_cover';
    public const    USER_UPDATE_INFORMATION_ENTITY_TYPE  = 'user_update_information';
    public const    USER_AVATAR_SIGN_UP                  = 'user_signup_avatar';
    public const    USER_UPDATE_RELATIONSHIP_ENTITY_TYPE = 'user_update_relationship';
    protected const DEFAULT_TAB_ABOUT                    = 'about';
    protected const DEFAULT_TAB_HOME                     = 'home';

    /** @var array<mixed> */
    public $nestedAttributes = [
        'profile',
    ];

    public $incrementing = false;

    /**
     * This is use for roles, permissions. Please do not remove this.
     *
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id',
        'user_name',
        'full_name',
        'first_name',
        'last_name',
        'search_name',
        'total_friend',
        'total_follower',
        'total_following',
        'email',
        'phone_number',
        'password',
        'is_featured',
        'is_invisible',
        'approve_status',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'phone_number_verified_at',
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_featured'       => 'boolean',
        'is_invisible'      => 'boolean',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'id', 'id');
    }

    public function userActivity(): HasOne
    {
        return $this->hasOne(UserActivity::class, 'id', 'id');
    }

    public function toUserResource(): array
    {
        $profile = $this->profile;

        return [
            'entity_type'    => $this->entityType(),
            'user_name'      => $this->user_name,
            'name'           => $this->display_name,
            'avatar_file_id' => $profile?->avatar_file_id,
            'avatar_id'      => $profile?->avatar_id,
            'is_featured'    => $this->is_featured ?? 0,
            'gender'         => $profile != null ? $profile->gender_id : 0,
        ];
    }

    public function userId(): int
    {
        return $this->{$this->primaryKey};
    }

    public function userType(): string
    {
        return self::ENTITY_TYPE;
    }

    public function ownerId(): int
    {
        return $this->{$this->primaryKey};
    }

    public function ownerType(): string
    {
        return self::ENTITY_TYPE;
    }

    public function toPrivacyLists()
    {
        return [
            [
                'item_id'      => $this->entityId(),
                'item_type'    => $this->entityType(),
                'user_id'      => $this->entityId(),
                'user_type'    => $this->entityType(),
                'owner_id'     => $this->entityId(),
                'owner_type'   => $this->entityType(),
                'privacy'      => MetaFoxPrivacy::ONLY_ME,
                'privacy_type' => 'user_private',
            ], [
                'item_id'      => $this->entityId(),
                'item_type'    => $this->entityType(),
                'user_id'      => $this->entityId(),
                'user_type'    => $this->entityType(),
                'owner_id'     => $this->entityId(),
                'owner_type'   => $this->entityType(),
                'privacy'      => MetaFoxPrivacy::FRIENDS,
                'privacy_type' => 'user_friends',
            ],
        ];
    }

    public function toActivitySubscription(): array
    {
        return [$this->entityId(), $this->entityId()];
    }

    public function toActivityFeed(): ?FeedAction
    {
        if (is_running_unit_test()) {
            return null;
        }

        $status = $this->isApproved() && $this->hasVerified()
            ? MetaFoxConstant::ITEM_STATUS_APPROVED
            : MetaFoxConstant::ITEM_STATUS_PENDING;

        return new FeedAction([
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->ownerId(),
            'owner_type' => $this->ownerType(),
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'type_id'    => $this->entityType(),
            'status'     => $status,
        ]);
    }

    public function toPrivacyItem(): array
    {
        return [
            [
                $this->entityId(),
                MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                MetaFoxPrivacy::PRIVACY_NETWORK_PUBLIC,
            ],
            [
                $this->entityId(),
                MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                MetaFoxPrivacy::PRIVACY_NETWORK_MEMBER,
            ],
            [
                $this->entityId(),
                MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                MetaFoxPrivacy::PRIVACY_NETWORK_FRIEND_OF_FRIENDS,
            ],
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Find the user instance for the given username.
     * Can login by user_name, email
     * Laravel/passport feature. Do not remove this.
     *
     * @param string $username
     *
     * @return User|null
     * @throws AuthorizationException
     * @throws ValidateUserException
     */
    public function findForPassport(string $username)
    {
        /** @var UserRepository $repository */
        $repository = resolve(UserRepositoryInterface::class);

        /** @var User $user */
        $query = $this
            ->newModelInstance()
            ->newQuery()
            ->where('user_name', $repository->likeOperator(), $username)
            ->orWhere('email', $repository->likeOperator(), $username);

        if (Settings::get('user.enable_phone_number_registration')) {
            $query->orWhere('phone_number', $username);
        }

        $user = $query->first();
        if (!$user instanceof self) {
            return null;
        }

        return $user;
    }

    public function canBeBlocked(): bool
    {
        return $this->hasPermissionTo('user.can_be_blocked_by_others');
    }

    public function checkPostBy(ContractUser $user, Content $content = null): bool
    {
        if ($content instanceof Content) {
            if ($content instanceof ResourcePostOnOwner) {
                return true;
            }

            if ($content instanceof ActionEntity) {
                return true;
            }

            // In case item is always public, we allow to create
            if (!$content instanceof HasPrivacy) {
                return true;
            }

            if ($user->entityId() == $this->userId()) {
                return true;
            }

            if ($user->hasPermissionTo('user.moderate')) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function user()
    {
        return $this->morphTo(self::class, null, $this->getKeyName(), $this->getKeyName())->withTrashed();
    }

    public function userEntity()
    {
        return $this->belongsTo(UserEntity::class, $this->getKeyName(), $this->getKeyName())->withTrashed();
    }

    public function owner()
    {
        return $this->morphTo(self::class, null, $this->getKeyName(), $this->getKeyName())->withTrashed();
    }

    public function ownerEntity()
    {
        return $this->belongsTo(UserEntity::class, $this->getKeyName(), $this->getKeyName())->withTrashed();
    }

    public function notificationEmail(): string
    {
        return $this->email ?? '';
    }

    public function notificationPhoneNumber(): string
    {
        return $this->phone_number ?? '';
    }

    public function notificationUserName(): string
    {
        return $this->user_name;
    }

    public function notificationFullName(): string
    {
        return $this->display_name;
    }

    public function getUserDescription(): string
    {
        $profile = $this->profile;
        if ($profile != null) {
            if ($profile->status != null) {
                return $profile->status;
            }
        }

        return '';
    }

    public function getPrivacyPostBy(): int
    {
        return MetaFoxPrivacy::FRIENDS;
    }

    public function toSearchable(): ?array
    {
        if (!$this->hasVerified()) {
            return null;
        }

        if (!$this->isApproved()) {
            return null;
        }

        $data = [
            'title' => $this->full_name,
            'text'  => $this->full_name,
        ];

        if ($this->is_invisible) {
            Arr::set($data, 'status', 'hidden');
        }

        return $data;
    }

    public function toTitle(): string
    {
        return $this->display_name ?? '';
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl($this->user_name);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl($this->user_name);
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileResourceUrl($this->entityType(), $this->entityId());
    }

    /**
     * @inheritDoc
     */
    public function hasNamedNotification(): ?string
    {
        return null;
    }

    public function hasFeedDetailPage(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasRemoveFeed(ContractUser $user, Content $content = null): bool
    {
        return false;
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new UserApproveNotification($this)];
    }

    /**
     * @inheritDoc
     */
    public function checkContentShareable(ContractUser $user, Content $content = null): bool
    {
        return $this->checkPostBy($user, $content);
    }

    /**
     * @param string $input
     *
     * @return bool
     */
    public function validateForPassportPasswordGrant($input): bool
    {
        $result = app('events')->dispatch('user.validate_password_for_grant', [$this, $input], true);

        if ($result !== null) {
            return $result;
        }

        return $this->validatePassword($input);
    }

    public function validatePassword($input): bool
    {
        if ($this->password) {
            // check by current password hash check
            return Hash::check($input, $this->password);
        }

        // add custom validation password rules.
        // find password, password_salt in user
        /* @var UserPassword $pwd */
        try {
            $pwd = UserPassword::query()->where('user_id', '=', $this->id)->first();
            if ($pwd) {
                return $pwd->validateForPassportPasswordGrant($input);
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * @return array
     *               Get custom profile value
     */
    public function customProfile(): array
    {
        return CustomProfile::denormalize($this, [
            'for_form'     => false,
            'section_type' => CustomField::SECTION_TYPE_USER,
        ]);
    }

    public function toPaymentSettingUrl(): string
    {
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.6', '<')) {
            return url_utility()->makeApiFullUrl('emoney');
        }

        return url_utility()->makeApiFullUrl('ewallet');
    }

    public function revokeAllTokens(): void
    {
        $refreshRepository = app(RefreshTokenRepository::class);
        $tokens            = $this->tokens()->get();
        $tokenModel        = Passport::tokenModel();

        foreach ($tokens as $token) {
            if (!$token instanceof $tokenModel) {
                continue;
            }
            $refreshRepository->revokeRefreshTokensByAccessTokenId($token->id);
            $token->revoke();
        }
    }

    public function transformRole(): string
    {
        $role = $this->getRole()?->name;

        return __p(
            'user::phrase.role_name_with_status',
            [
                'role'                             => $role,
                'approveStatus'                    => $this->approve_status,
                'isPendingVerification'            => $this->shouldVerifyEmailAddress() || $this->shouldVerifyPhoneNumber(),
                'isPendingVerificationEmail'       => $this->shouldVerifyEmailAddress(),
                'isPendingVerificationPhoneNumber' => $this->shouldVerifyPhoneNumber(),
            ]
        );
    }

    public function isApproved(): bool
    {
        return $this->approve_status == MetaFoxConstant::STATUS_APPROVED;
    }

    public function isPendingApproval(): bool
    {
        return $this->approve_status == MetaFoxConstant::STATUS_PENDING_APPROVAL;
    }

    public function isNotApproved(): bool
    {
        return $this->approve_status == MetaFoxConstant::STATUS_NOT_APPROVED;
    }

    public function getDescriptionAttribute()
    {
        return $this->getUserDescription();
    }

    public function preferredLocale(): ?string
    {
        return $this->profile->language_id;
    }

    /**
     * @param string|null $value
     */
    public function getFullNameAttribute(?string $value): string
    {
        if (empty($value)) {
            return ban_word()->clean($this->user_name);
        }

        return ban_word()->clean($value);
    }

    public function getDisplayNameAttribute(): string
    {
        return empty($this->full_name_raw) ? $this->user_name : $this->full_name_raw;
    }

    public function getFullNameRawAttribute(): string
    {
        return Arr::get($this->getAttributes(), 'full_name', MetaFoxConstant::EMPTY_STRING);
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('profile');

        return FacadesUser::getSummary($context, $this);
    }

    /**
     * @inheritDoc
     */
    public function isTaggingAllowed(): bool
    {
        /** @var UserPrivacyRepositoryInterface $repository */
        $repository         = resolve(UserPrivacyRepositoryInterface::class);
        $canBeTaggedPrivacy = $repository->getUserPrivacyByName($this->entityId(), 'user.can_i_be_tagged');

        if (null === $canBeTaggedPrivacy) {
            return true;
        }

        if ($canBeTaggedPrivacy->privacy == MetaFoxPrivacy::ONLY_ME) {
            return false;
        }

        return true;
    }

    /* Determine if the model has (one of) the given role(s).
     *
     * @param string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     */
    public function hasRole($roles, string $guard = null): bool
    {
        // @todo how to re-use this method because its called 2 times
        $userRoles = LoadReduce::remember(
            sprintf('user::roleIds(user:%s)', $this->id),
            fn () => $this->roles->map(fn ($item) => $item->id)->toArray()
        );

        return count(array_intersect($this->normalizeRoles($roles), $userRoles));
    }

    public function getOwnerAttribute()
    {
        return LoadReduce::getEntity('user', $this->id, fn () => $this->getRelationValue('owner'));
    }

    public function getUserAttribute()
    {
        return LoadReduce::getEntity('user', $this->id, fn () => $this->getRelationValue('user'));
    }

    public function getProfileAttribute()
    {
        return LoadReduce::getEntity(UserProfile::ENTITY_TYPE, $this->id, fn () => $this->getRelationValue('profile'));
    }

    public function getOwnerEntityAttribute()
    {
        return LoadReduce::getEntity('user', $this->id, fn () => $this->getRelationValue('ownerEntity'));
    }

    public function getUserEntityAttribute()
    {
        return LoadReduce::getEntity('user', $this->id, fn () => $this->getRelationValue('userEntity'));
    }

    private function normalizeRoles(mixed $roles): array
    {
        if ($roles instanceof Collection) {
            $roles = $roles->pluck('id')->toArray();
        }

        $roles = Arr::flatten(Arr::wrap($roles));

        return array_filter($roles, fn ($val) => is_numeric($val));
    }

    public function getDefaultTabMenu(): string
    {
        $defaultActiveTabMenu = self::DEFAULT_TAB_HOME;

        return localCacheStore()->rememberForever(
            sprintf(
                '%s::getDefaultTabMenu(%s:%s,landingPage:%s)',
                get_class($this),
                $this->entityType(),
                $this->entityId(),
                $defaultActiveTabMenu
            ),
            function () use ($defaultActiveTabMenu) {
                /** @var \Illuminate\Support\Collection $profileMenus */
                $profileMenus = resolve(MenuItemRepositoryInterface::class)
                    ->getMenuItemByMenuName('user.user.profileMenu', 'web', true);

                return $profileMenus->firstWhere('name', $defaultActiveTabMenu)
                    ? $defaultActiveTabMenu
                    : self::DEFAULT_TAB_ABOUT;
            }
        );
    }

    public function toSitemapUrl(): ?string
    {
        $url = $this->toUrl();

        if ($this->mustVerify()) {
            return null;
        }

        if (!$this->isApproved()) {
            return null;
        }

        return $url;
    }

    public function getSharedFeedAvatarTypeIdAttribute(): string
    {
        return self::USER_UPDATE_AVATAR_ENTITY_TYPE;
    }

    public function getSharedFeedCoverTypeIdAttribute(): string
    {
        return self::USER_UPDATE_COVER_ENTITY_TYPE;
    }

    public function toFeaturedData(): ?array
    {
        return [];
    }
}
