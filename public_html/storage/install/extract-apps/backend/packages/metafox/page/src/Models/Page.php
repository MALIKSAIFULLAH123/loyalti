<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use MetaFox\Authorization\Traits\HasRoles;
use MetaFox\Page\Database\Factories\PageFactory;
use MetaFox\Page\Notifications\PageApproveNotification;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Support\Facade\Page as PageSupportFacade;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasTotalMember;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\PostAs;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\PrivacyList;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Support\HasUser;
use MetaFox\Platform\Traits\Eloquent\Model\HasAvatarMorphTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasAvatarTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasCoverMorphTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasFollowTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Platform\Traits\Notifiable;
use MetaFox\User\Models\UserEntity;

/**
 * Class Page.
 *
 * @mixin Builder
 * @property        int                      $id
 * @property        int                      $view_id
 * @property        int                      $category_id
 * @property        int                      $total_member
 * @property        int                      $total_admin
 * @property        bool                     $is_invited
 * @property        int                      $total_share
 * @property        int                      $is_featured
 * @property        int                      $is_sponsor
 * @property        string                   $name
 * @property        string                   $profile_name
 * @property        string                   $phone
 * @property        string                   $external_link
 * @property        string                   $landing_page
 * @property        string                   $summary
 * @property        string                   $page_image
 * @property        string                   $cover_photo_position
 * @property        float                    $location_latitude
 * @property        float                    $location_longitude
 * @property        string                   $location_name
 * @property        string                   $created_at
 * @property        string                   $updated_at
 * @property        Category                 $category
 * @property        User|null                $user
 * @property        User|null                $owner
 * @property        Collection               $members
 * @property        Collection               $invites
 * @property        PageText|null            $pageText
 * @property        PageClaim                $pageClaim
 * @property        array<int|string, mixed> $covers
 * @property        array<int|string, mixed> $avatars
 * @method   static PageFactory              factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Page extends Model implements
    User,
    PrivacyList,
    IsActivitySubscriptionInterface,
    IsNotifiable,
    PostAs,
    PostBy,
    HasPrivacy,
    HasPrivacyMember,
    HasResourceStream,
    HasApprove,
    HasFeature,
    HasSponsor,
    HasTotalMember,
    HasTotalShare,
    HasAvatarMorph,
    HasCoverMorph,
    HasLocationCheckin,
    HasGlobalSearch
{
    use Notifiable;
    use HasUserMorph;
    use HasFactory;
    use HasNestedAttributes;
    use HasAvatarMorphTrait;
    use HasCoverMorphTrait;
    use HasRoles;
    use HasUser;
    use HasAvatarTrait;
    use HasApiTokens;
    use HasFilterTagUserTrait;
    use HasFollowTrait;

    /**
     * This is use for roles, permissions. Please do not remove this.
     *
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * @var string
     */
    protected $table = 'pages';

    public const ENTITY_TYPE = 'page';

    public $incrementing = false;

    public const PAGE_ADMINS  = 'page_admins';
    public const PAGE_MEMBERS = 'page_members';

    public const ADMIN_PRIVACY  = MetaFoxPrivacy::CUSTOM;
    public const MEMBER_PRIVACY = MetaFoxPrivacy::FRIENDS;

    public const PAGE_UPDATE_PROFILE_ENTITY_TYPE = 'page_update_avatar';
    public const PAGE_UPDATE_COVER_ENTITY_TYPE   = 'page_update_cover';

    protected const DEFAULT_TAB_ABOUT          = 'about';
    protected const DEFAULT_TAB_HOME           = 'home';
    public const    ORDERING_FOR_INFO_SETTING  = [
        'name'                   => 1,
        'category_id'            => 2,
        'landing_page'           => 3,
        'profile_name'           => 4,
        'external_link'          => 5,
        'additional_information' => 6,
    ];
    public const    ORDERING_FOR_ABOUT_SETTING = [
        'text'     => 1,
        'location' => 2,
    ];
    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'category_id',
        'user_id',
        'user_type',
        'name',
        'profile_name',
        'phone',
        'external_link',
        'landing_page',
        'is_approved',
        'is_featured',
        'is_sponsor',
        'total_member',
        'total_share',
        'total_invite',
        'total_admin',
        'privacy',
        'avatar_type',
        'avatar_id',
        'avatar_file_id',
        'cover_type',
        'cover_id',
        'cover_file_id',
        'cover_photo_position',
        'location_latitude',
        'location_longitude',
        'location_name',
        'updated_at',
        'created_at',
        'deleted_at',
        'total_follower',
        'total_following',
        'location_address',
    ];

    /**
     * @var array<string, mixed>
     */
    public array $nestedAttributes = [
        'pageText' => ['text', 'text_parsed'],
    ];

    // where to store resources ?
    public array $fileColumns = [
        'avatar_file_id' => 'photo',
        'cover_file_id'  => 'photo',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'summary',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'location_latitude'  => 'float',
        'location_longitude' => 'float',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toPrivacyLists()
    {
        return [
            // Page members.
            [
                'item_id'      => $this->entityId(),
                'item_type'    => $this->entityType(),
                'user_id'      => $this->userId(),
                'user_type'    => $this->userType(),
                'owner_id'     => $this->entityId(),
                'owner_type'   => $this->entityType(),
                'privacy_type' => self::PAGE_MEMBERS,
                'privacy'      => MetaFoxPrivacy::FRIENDS,
            ],
            // Page admins.
            [
                'item_id'      => $this->entityId(),
                'item_type'    => $this->entityType(),
                'user_id'      => $this->userId(),
                'user_type'    => $this->userType(),
                'owner_id'     => $this->entityId(),
                'owner_type'   => $this->entityType(),
                'privacy_type' => self::PAGE_ADMINS,
                'privacy'      => MetaFoxPrivacy::CUSTOM,
            ],
        ];
    }

    public function toUserResource(): array
    {
        return [
            'entity_type'    => $this->entityType(),
            'name'           => $this->name,
            'user_name'      => $this->profile_name != '' ? $this->profile_name : null,
            'avatar_file_id' => $this->avatar_file_id,
            'is_featured'    => $this->is_featured ?? 0,
        ];
    }

    public function toActivitySubscription(): array
    {
        return [$this->userId(), $this->entityId()];
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    /**
     * @return HasOne
     */
    public function pageText(): HasOne
    {
        return $this->hasOne(PageText::class, 'id', 'id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(PageMember::class, 'page_id', 'id');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(PageInvite::class, 'page_id', 'id');
    }

    public function pageClaim(): HasOne
    {
        return $this->hasOne(PageClaim::class, 'page_id', 'id');
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }

    public function canBeBlocked(): bool
    {
        return false;
    }

    public function getPostAsDefault(): int
    {
        return $this->entityId();
    }

    public function checkPostAs(User $user): bool
    {
        if ($user->entityId() == $this->userId()) {
            return true;
        }

        return PrivacyPolicy::hasAbilityOnOwner($user, $this, MetaFoxPrivacy::CUSTOM, self::PAGE_ADMINS);
    }

    public function checkPostBy(User $user, Content $content = null): bool
    {
        if ($user->entityId() == $this->userId()) {
            return true;
        }

        if ($user->hasPermissionTo('page.moderate')) {
            return true;
        }

        return $this->isApproved();
    }

    public function isMember(User $user): bool
    {
        return PrivacyPolicy::hasAbilityOnOwner($user, $this, MetaFoxPrivacy::FRIENDS, self::PAGE_MEMBERS);
    }

    public function isAdmin(User $user): bool
    {
        if ($this->isUser($user)) {
            return true;
        }

        return PrivacyPolicy::hasAbilityOnOwner($user, $this, MetaFoxPrivacy::CUSTOM, self::PAGE_ADMINS);
    }

    public function isModerator(User $user): bool
    {
        return false;
    }

    public function getPrivacyItem(): int
    {
        return MetaFoxPrivacy::EVERYONE;
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PrivacyStream::class, 'item_id', 'id');
    }

    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function getSummaryAttribute(): string
    {
        return $this->category?->name ?? '';
    }

    public function getPrivacyPostBy(): int
    {
        return MetaFoxPrivacy::EVERYONE;
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $modelText = $this->pageText;

        return [
            'title' => $this->name,
            'text'  => $modelText ? $modelText->text_parsed : '',
        ];
    }

    public function toTitle(): string
    {
        $title = Arr::get($this->attributes, 'name', MetaFoxConstant::EMPTY_STRING);

        return ban_word()->clean($title);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->toTitle();
    }

    public function ownerId(): int
    {
        return $this->id;
    }

    public function ownerType(): string
    {
        return self::ENTITY_TYPE;
    }

    public function owner()
    {
        return $this->belongsTo(self::class, 'id', 'id')->withTrashed();
    }

    public function ownerEntity()
    {
        return $this->belongsTo(UserEntity::class, 'id', 'id')->withTrashed();
    }

    public function hasPermissionValue($permission): bool
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return false;
        }

        return $user->hasPermissionValue($permission);
    }

    public function getPermissionValue($permission)
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return false;
        }

        return $user->getPermissionValue($permission);
    }

    /**
     * @inheritDoc
     */
    public function hasNamedNotification(): ?string
    {
        return $this->entityType();
    }

    public function getRepresentativePrivacyDetail(int $privacy): ?array
    {
        if (MetaFoxPrivacy::EVERYONE != $privacy) {
            return null;
        }

        return [
            'privacy_icon' => $privacy,
            'tooltip'      => __p('page::phrase.public_page'),
        ];
    }

    public function toUrl(): ?string
    {
        $userName = $this->ownerEntity->user_name ?? $this->entityType() . '/' . $this->entityId();

        return url_utility()->makeApiFullUrl($userName);
    }

    public function toLink(): ?string
    {
        $userName = $this->ownerEntity->user_name ?? $this->entityType() . '/' . $this->entityId();

        return url_utility()->makeApiUrl($userName);
    }

    public function hasFeedDetailPage(): bool
    {
        return true;
    }

    public function admins(): HasMany
    {
        return $this->hasMany(PageMember::class, 'page_id')
            ->where('member_type', '=', PageMember::ADMIN);
    }

    /**
     * @inheritDoc
     */
    public function hasRemoveFeed(User $user, Content $content = null): bool
    {
        return false;
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new PageApproveNotification($this)];
    }

    /**
     * @inheritDoc
     */
    public function checkContentShareable(User $user, Content $content = null): bool
    {
        return $this->checkPostBy($user, $content);
    }

    public function isOwner(User $user): bool
    {
        /*
         * This is page creator
         */
        if ($user->entityId() == $this->userId()) {
            return true;
        }

        /*
         * This is login as page
         */
        if ($user->entityId() == $this->entityId()) {
            return true;
        }

        return false;
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getRoleLabel(User $user): ?string
    {
        if ($this->isAdmin($user)) {
            return __p('page::phrase.label_role_admin');
        }

        return null;
    }

    public function toSponsorData(): ?array
    {
        return [
            'title' => __p('page::phrase.sponsor_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function roleId(): int
    {
        return $this->user?->getRole()?->id ?? 0;
    }

    public function toOGDescription(?User $context = null): ?string
    {
        $this->loadMissing('pageText');
        $pageText = $this->pageText;

        if (!$pageText instanceof PageText) {
            return null;
        }

        return strip_tags($pageText->text_parsed);
    }

    public function isInvited(): HasOne
    {
        return $this->hasOne(PageInvite::class, 'page_id', 'id')
            ->where('owner_id', '=', Auth::user()?->id ?? 0);
    }

    public function getIsInvitedAttribute()
    {
        return LoadReduce::get(
            sprintf('user::isInvited(user:%s,owner:%s)', Auth::user()?->id, $this->id),
            fn () => null != $this->getRelationValue('isInvited')
        );
    }

    public function filterMentionUsersByOwner(User $context, User $user, array $mentionedUserIds): array
    {
        if (!count($mentionedUserIds)) {
            return [];
        }

        if (!policy_check(PagePolicy::class, 'viewAny', $context)) {
            return [];
        }

        /*
         * In case user type is User
         */
        if ($user->entityId() != $this->entityId()) {
            return $this->fallbackMentionUsers($context, $user, $mentionedUserIds);
        }

        /**
         * In case user type is Page. It means posting as page.
         */
        $newIds = [];

        $userEntities = UserEntity::query()
            ->whereIn('id', $mentionedUserIds)
            ->get();

        $pages = $userEntities->filter(function ($entity) {
            return $entity->entity_type == $this->entityType() && $entity->entityId() != $this->entityId();
        });

        if ($pages->count()) {
            $newIds = array_merge($newIds, $pages->pluck('id')->toArray());
        }

        $users = $userEntities->filter(function ($entity) {
            return $entity->entity_type == \MetaFox\User\Models\User::ENTITY_TYPE;
        });

        if ($users->count()) {
            $members = PageMember::query()
                ->whereIn('user_id', $users->pluck('id')->toArray())
                ->where('page_id', $this->entityId())
                ->get();

            if ($members->count()) {
                $newIds = array_merge($newIds, $members->pluck('user_id')->toArray());
            }
        }

        $publicGroups = app('events')->dispatch('group.filter_public_group', [$userEntities], true);

        if ($publicGroups instanceof Collection && $publicGroups->count()) {
            $newIds = array_merge($newIds, $publicGroups->pluck('id')->toArray());
        }

        return array_unique($newIds);
    }

    public function filterTagUsersByOwner(User $context, User $user, array $taggedFriendIds): array
    {
        if (!policy_check(PagePolicy::class, 'viewAny', $context)) {
            return [];
        }

        if ($user->entityId() != $this->entityId()) {
            return $this->fallbackTagUsers($context, $user, $taggedFriendIds);
        }

        $userEntities = UserEntity::query()
            ->whereIn('id', $taggedFriendIds)
            ->get();

        if (!$userEntities->count()) {
            return [];
        }

        $members = PageMember::query()
            ->whereIn('user_id', $userEntities->pluck('id')->toArray())
            ->where('page_id', $this->entityId())
            ->get();

        return array_unique($members->pluck('user_id')->toArray());
    }

    public function getRepresentativePrivacy(): ?int
    {
        return $this->privacy;
    }

    /**
     * @param  User   $user
     * @return string
     * @deprecated version greater than v5.15 => remove this method.
     */
    public function getDefaultTabMenu(User $user): string
    {
        $defaultActiveTabMenu = $this->landing_page ?? self::DEFAULT_TAB_HOME;

        if (!$this->isApproved()) {
            return self::DEFAULT_TAB_ABOUT;
        }

        return localCacheStore()->rememberForever(
            PageSupportFacade::getCacheKeyDefaultTabActive($this),
            function () use ($defaultActiveTabMenu) {
                $integrateRepository = resolve(IntegratedModuleRepositoryInterface::class);
                $menus               = $integrateRepository->getModules($this->entityId());
                $item                = $menus->firstWhere('tab', $defaultActiveTabMenu);

                return $item instanceof IntegratedModule
                    ? $item->tab
                    : self::DEFAULT_TAB_ABOUT;
            }
        );
    }

    public function getSharedFeedAvatarTypeIdAttribute(): string
    {
        return self::PAGE_UPDATE_PROFILE_ENTITY_TYPE;
    }

    public function getSharedFeedCoverTypeIdAttribute(): string
    {
        return self::PAGE_UPDATE_COVER_ENTITY_TYPE;
    }

    public function getHasPhotoParentLinkAttribute(): bool
    {
        return true;
    }

    public function getHasVideoParentLinkAttribute(): bool
    {
        return true;
    }

    public function getHasLiveVideoParentLinkAttribute(): bool
    {
        return true;
    }

    public function toFeaturedData(): ?array
    {
        return [];
    }

    public function notificationEmail(): string
    {
        return $this->user?->email ?? MetaFoxConstant::EMPTY_STRING;
    }

    public function notificationPhoneNumber(): string
    {
        return $this->user?->phone_number ?? MetaFoxConstant::EMPTY_STRING;
    }

    public function notificationUserName(): string
    {
        return $this->profile_name ?? $this->user?->user_name ?? MetaFoxConstant::EMPTY_STRING;
    }

    public function notificationFullName(): string
    {
        return ban_word()->clean($this->name) ?? $this->user?->full_name ?? MetaFoxConstant::EMPTY_STRING;
    }
}
