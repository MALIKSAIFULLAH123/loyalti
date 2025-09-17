<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Photo\Database\Factories\PhotoFactory;
use MetaFox\Photo\Notifications\NewPhotoToFollowerNotification;
use MetaFox\Photo\Notifications\PhotoApproveNotification;
use MetaFox\Platform\Contracts\ActivityFeedForm;
use MetaFox\Platform\Contracts\ActivityFeedSourceCanEditAttachment;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasFeedContent;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasResourceCategory;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasSponsorInFeed;
use MetaFox\Platform\Contracts\HasTaggedFriendWithPosition;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasMedia;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\User\Models\User as UserModels;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Notifications\ProfileUpdatedByAdmin;

/**
 * Class Photo.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @mixin Builder
 * @property        int                           $id
 * @property        int                           $album_id
 * @property        int                           $album_type
 * @property        int                           $group_id
 * @property        int                           $type_id
 * @property        string                        $title
 * @property        string                        $image_file_id
 * @property        string                        $item_type
 * @property        string                        $module_id
 * @property        int                           $user_id
 * @property        string                        $user_type
 * @property        int                           $owner_id
 * @property        string                        $owner_type
 * @property        string                        $privacy
 * @property        int                           $total_view
 * @property        int                           $total_like
 * @property        int                           $total_dislike
 * @property        int                           $total_comment
 * @property        int                           $total_share
 * @property        int                           $total_tag
 * @property        int                           $total_download
 * @property        int                           $total_vote
 * @property        float                         $total_rating
 * @property        int                           $mature
 * @property        int                           $allow_rate
 * @property        int                           $is_approved
 * @property        int                           $is_featured
 * @property        int                           $is_sponsor
 * @property        bool                          $is_cover
 * @property        bool                          $is_cover_photo
 * @property        bool                          $is_profile_photo
 * @property        bool                          $sponsor_in_feed
 * @property        int                           $is_temp
 * @property        mixed                         $file_id
 * @property        int                           $ordering
 * @property        int                           $location_latitude
 * @property        int                           $location_longitude
 * @property        string                        $location_name
 * @property        string                        $content
 * @property        string                        $created_at
 * @property        string                        $updated_at
 * @property        \MetaFox\Activity\Models\Feed $activity_feed
 * @property        ContractUser                  $user
 * @property        ContractUser                  $owner
 * @property        Album                         $album
 * @property        Collection                    $categories
 * @property        Collection                    $activeCategories
 * @property        PhotoInfo|null                $photoInfo
 * @property        PhotoGroup|null               $group
 * @property        StorageFile                   $fileItem
 * @property        string                        $photo_type
 * @property        string|null                   $owner_navigation_link
 * @property        string|null                   $featured_title
 * @property        string|null                   $sponsored_title
 * @method   static PhotoFactory                  factory(...$parameters)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Photo extends Model implements
    Media,
    ActivityFeedSourceCanEditAttachment,
    ActivityFeedForm,
    AppendPrivacyList,
    HasPrivacy,
    HasResourceStream,
    HasResourceCategory,
    HasApprove,
    HasFeature,
    HasSponsor,
    HasSponsorInFeed,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasTotalView,
    HasLocationCheckin,
    HasThumbnail,
    HasTaggedFriendWithPosition,
    HasSavedItem,
    HasGlobalSearch,
    HasHashTag
{
    use HasContent;
    use HasOwnerMorph;
    use HasUserMorph;
    use AppendPrivacyListTrait;
    use HasNestedAttributes;
    use HasFactory;
    use HasThumbnailTrait;
    use HasTaggedFriendTrait;
    use SoftDeletes;
    use HasMedia;

    public const ENTITY_TYPE            = 'photo';
    public const MATURE_CONTENT_NO      = 0;
    public const MATURE_CONTENT_WARNING = 1;
    public const MATURE_CONTENT_STRICT  = 2;

    public const IS_PENDING = 0;

    public const TYPE_AVATAR = 'avatar';
    public const TYPE_COVER  = 'cover';
    public const TYPE_NORMAL = 'normal';

    /** @var array<int|string, array<int, string>|string> */
    public array $nestedAttributes = [
        'categories',
        'photoInfo' => [
            'text',
            'text_parsed',
        ],
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'album_id',
        'album_type',
        'group_id',
        'type_id',
        'title',
        'item_type',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'total_view',
        'total_like',
        'total_dislike',
        'total_share',
        'total_tag',
        'total_download',
        'image_file_id',
        'total_vote',
        'total_rating',
        'mature',
        'allow_rate',
        'is_featured',
        'is_sponsor',
        'is_approved',
        'location_name',
        'location_latitude',
        'location_longitude',
        'content',
        'is_temp',
        'ordering',
        'sponsor_in_feed',
        'updated_at',
        'created_at',
        'in_process',
        'total_tag_friend',
        'total_pending_reply',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'location_address',
    ];

    protected static function booted()
    {
        static::saving(function (self $photo) {
            if (null === $photo->item_type) {
                $photo->item_type = 'photo';
            }
        });
    }

    /**
     * @var string[]
     */
    protected $appends = [
        'is_cover',
        'is_cover_photo',
        'is_profile_photo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_featured'     => 'boolean',
        'is_sponsor'      => 'boolean',
        'is_approved'     => 'boolean',
        'is_temp'         => 'boolean',
        'sponsor_in_feed' => 'boolean',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    public function getImageUrlAttribute(): string
    {
        return app('storage')->getFile($this->image_file_id)->url;
    }

    public function getDownloadUrlAttribute(): string
    {
        return app('storage')->getAs($this->image_file_id);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function getAlbumAttribute()
    {
        return LoadReduce::getEntity('photo_album', $this->album_id, fn () => $this->getRelationValue('album'));
    }

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function toActivityFeed(): ?FeedAction
    {
        if (!$this->isApproved() && !$this->owner instanceof HasPendingMode) {
            return null;
        }

        if ($this->group_id > 0) {
            return null;
        }

        if ($this->album_id > 0) {
            // If photo is in a normal album, don't create feed.
            if (Album::NORMAL_ALBUM == $this->album_type) {
                return null;
            }
        }

        if (null === $this->user) {
            return null;
        }

        $typeId = $this->activity_type_id ?: $this->entityType();

        if ($typeId != $this->entityType() && $this->userId() != $this->ownerId() && $this->owner instanceof UserModels) {
            if ($this->user->hasSuperAdminRole()) {
                Notification::send($this->owner, new ProfileUpdatedByAdmin($this->owner));
            }

            return null;
        }

        return new FeedAction([
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->ownerId(),
            'owner_type' => $this->ownerType(),
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'type_id'    => $typeId,
            'privacy'    => $this->privacy,
            'content'    => $this->getFeedContent(),
        ]);
    }

    protected static function newFactory(): PhotoFactory
    {
        return PhotoFactory::new();
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'photo_category_data',
            'item_id',
            'category_id'
        )->using(CategoryData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function activeCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'photo_category_data',
            'item_id',
            'category_id'
        )->where('is_active', Category::IS_ACTIVE)->using(CategoryData::class);
    }

    /**
     * @return HasMany
     */
    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PhotoPrivacyStream::class, 'item_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function photoInfo(): HasOne
    {
        return $this->hasOne(PhotoInfo::class, 'id', 'id');
    }

    /**
     * @return HasOne
     */
    public function fileItem(): HasOne
    {
        return $this->hasOne(StorageFile::class, 'id', 'image_file_id');
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(PhotoGroup::class);
    }

    public function getGroupAttribute()
    {
        return LoadReduce::getEntity('photo_set', $this->group_id, fn () => $this->getRelationValue('group'));
    }

    /**
     * @return array<int, mixed>
     */
    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function getFeedContent(): ?string
    {
        return $this->content;
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    public function getIsCoverAttribute(): bool
    {
        if ($this->album == null) {
            return false;
        }

        return $this->album->cover_photo_id == $this->entityId();
    }

    public function getIsCoverPhotoAttribute(): bool
    {
        if ($this->album == null) {
            return false;
        }

        return $this->album->album_type == Album::COVER_ALBUM;
    }

    public function getIsProfilePhotoAttribute(): bool
    {
        if ($this->album == null) {
            return false;
        }

        return $this->album->album_type == Album::PROFILE_ALBUM;
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => ban_word()->clean($this->title),
            'image'          => $this->images,
            'item_type_name' => __p("photo::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => 1,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $reactItem = $this->reactItem();
        $text      = $reactItem instanceof HasFeedContent ? $reactItem->getFeedContent() : MetaFoxConstant::EMPTY_STRING;

        return [
            'title' => $this->title,
            'text'  => $text,
        ];
    }

    public function toTitle(): string
    {
        if ($this->is_profile_photo || $this->is_cover_photo) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $title = Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);

        return ban_word()->clean($title);
    }

    public function reactItem()
    {
        if (!$this->isApproved()) {
            return $this;
        }

        if (!$this->group || $this->group->approvedItems->count() > 1) {
            return $this;
        }

        return $this->group;
    }

    public function albumItem(): MorphOne
    {
        return $this->morphOne(AlbumItem::class, 'detail', 'item_type', 'item_id');
    }

    public function groupItem(): MorphOne
    {
        return $this->morphOne(PhotoGroupItem::class, 'detail', 'item_type', 'item_id');
    }

    /**
     * @param UserEntity $user
     * @param UserEntity $owner
     * @param bool       $isMention
     *
     * @return string
     */
    public function toCallbackMessage(UserEntity $user, UserEntity $owner, bool $isMention = false): string
    {
        $yourName     = $user->name;
        $friendName   = $owner->name;
        $itemUser     = $this->user;
        $itemUserId   = $this->userId();
        $itemUserName = $this->user->full_name;
        $notifiable   = $itemUser;

        if (method_exists($this, 'getTagFriendNotifiable')) {
            $notifiable = $this->getTagFriendNotifiable();
        }

        // $user tagged $owner in photo of $owner => $owner received a notification
        if ($owner->entityId() == $notifiable->entityId() && $itemUserId == $notifiable->entityId()) {
            return __p('core::phrase.username_tagged_you_in_your_photo', [
                'username' => $yourName,
            ]);
        }

        // $user tagged themself in photo of $owner => $owner received a notification
        if ($user->entityId() == $owner->entityId()) {
            return $this->handleMessageForGender($user->gender, $yourName);
        }

        // $user tagged $owner in photo of $itemUser => $owner received a notification
        if ($notifiable->entityId() == $owner->entityId() && $notifiable->entityId() != $itemUserId && $user->entityId() != $itemUserId) {
            return __p('core::phrase.username_tagged_you_in_photo_of_item_user', [
                'username'     => $yourName,
                'itemUserName' => $itemUserName,
            ]);
        }

        // $user tagged user $owner in photo of $itemUser => $itemUser received a notification
        if ($notifiable->entityId() != $owner->entityId() && $notifiable->entityId() == $itemUserId && $user->entityId() != $itemUserId) {
            return __p('core::phrase.username_tagged_friendname_in_your_photo', [
                'username'   => $yourName,
                'friendname' => $friendName,
            ]);
        }

        // $user tagged $owner in photo of $user => $user received a notification
        if ($user->entityId() == $itemUserId && $notifiable->entityId() == $owner->entityId()) {
            return __p('core::phrase.username_tagged_you_in_a_photo', [
                'username' => $yourName,
            ]);
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    /**
     * @param int    $gender
     * @param string $yourName
     *
     * @return string
     */
    private function handleMessageForGender(int $gender, string $yourName): string
    {
        return __p('core::phrase.username_tagged_yourself_in_your_photo', [
            'gender'   => $gender,
            'username' => $yourName,
        ]);
    }

    public function hasTagStream(): bool
    {
        return false;
    }

    /**
     * @return array<int, mixed>
     */
    public function toApprovedNotification(): array
    {
        return [$this->user, new PhotoApproveNotification($this)];
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl(sprintf('/%s/%s/%s', 'photo', $this->entityId(), $this->toSlug()));
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl(sprintf('/%s/%s/%s', 'photo', $this->entityId(), $this->toSlug()));
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->toTitle();
    }

    public function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'title');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($title), language: null);
    }

    public function toFollowerNotification(): ?array
    {
        $data = [
            'owner'   => $this->owner,
            'exclude' => [$this->user],
        ];

        if (!$this->is_cover_photo && !$this->is_profile_photo) {
            return null;
        }

        if ($this->owner instanceof HasPrivacyMember) {
            return $data;
        }

        if ($this->userId() != $this->ownerId()) {
            return null;
        }

        $message = __p('photo::notification.user_name_updated_their_cover_or_profile_picture', [
            'gender'    => $this->userEntity->possessive_gender,
            'user_name' => $this->userEntity->name,
            'type'      => $this->is_cover_photo ? Album::COVER_ALBUM : Album::PROFILE_ALBUM,
        ]);

        $notification = new NewPhotoToFollowerNotification();

        Arr::set($data, 'type', $notification->getType());

        return Arr::set($data, 'message', $message);
    }

    public function toFollowerCallbackMessage(?string $locale = null): ?string
    {
        if (!$this->owner instanceof HasPrivacyMember) {
            return null;
        }

        if (!$this->is_cover_photo && !$this->is_profile_photo) {
            return null;
        }

        return __p('photo::notification.user_name_updated_parent_cover_or_profile_picture', [
            'owner_type' => __p_type_key($this->ownerType()),
            'type'       => $this->is_cover_photo ? Album::COVER_ALBUM : Album::PROFILE_ALBUM,
            'owner_name' => $this->ownerEntity->name,
        ], $locale);
    }

    /**
     * @return array<int, mixed>
     */
    public function toFeedOGImages(): array
    {
        $thumbnails = $this->images;

        return is_array($thumbnails) ? $thumbnails : [];
    }

    public function toSponsorData(): ?array
    {
        return [
            'title' => __p('photo::phrase.sponsor_title_photo', [
                'title' => $this->sponsored_title,
            ]),
        ];
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('photoInfo');

        $photoInfo = $this->photoInfo;

        if (!$photoInfo instanceof PhotoInfo) {
            return null;
        }

        return strip_tags($photoInfo->text_parsed);
    }

    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'photo_tag_data',
            'item_id',
            'tag_id'
        )->using(PhotoTagData::class);
    }

    public function getUnsupportChangeFeedPrivacyAttribute(): bool
    {
        if (in_array($this->type_id, [Album::PROFILE_ALBUM, Album::COVER_ALBUM])) {
            return false;
        }

        return true;
    }

    public function getSharedFeedTypeIdAttribute(): ?string
    {
        $albumType = $this->getAttributeFromArray('album_type');

        $owner = $this->owner;

        if (null === $owner) {
            return null;
        }

        if ($albumType == Album::PROFILE_ALBUM) {
            return $owner->shared_feed_avatar_type_id;
        }

        if ($albumType == Album::COVER_ALBUM) {
            return $owner->shared_feed_cover_type_id;
        }

        return null;
    }

    public function getPhotoTypeAttribute(): string
    {
        $albumType = $this->getAttributeFromArray('album_type');

        if (Album::PROFILE_ALBUM == $albumType) {
            return self::TYPE_AVATAR;
        }

        if (Album::COVER_ALBUM == $albumType) {
            return self::TYPE_COVER;
        }

        return self::TYPE_NORMAL;
    }

    public function isMatureContent(): bool
    {
        return (bool) $this->mature;
    }

    public function isStrictMatureContent(): bool
    {
        return $this->mature === self::MATURE_CONTENT_STRICT;
    }

    public function isWarningMatureContent(): bool
    {
        return $this->mature === self::MATURE_CONTENT_WARNING;
    }

    public function getOwnerNavigationLinkAttribute(): ?string
    {
        $owner = $this->owner;

        if (!$owner instanceof ContractUser) {
            return null;
        }

        if ($owner->entityType() === 'user') {
            return '/photo';
        }

        $hasParentLink = $owner->has_photo_parent_link;

        if (true === $hasParentLink) {
            return sprintf('%s/photo', $owner->toLink());
        }

        return method_exists($owner, 'toDiscussionUrl')
            ? $owner->toDiscussionUrl()
            : null;
    }

    public function toFeaturedData(): ?array
    {
        return [
            'title' => $this->featured_title,
        ];
    }

    public function getFeaturedTitleAttribute(): ?string
    {
        $title = Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);

        if (!is_string($title)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return ban_word()->clean(trim($title));
    }

    public function getSponsoredTitleAttribute(): ?string
    {
        $title = Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);

        if (!is_string($title)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return ban_word()->clean(trim($title));
    }

    public function toOGImages(): array
    {
        if ($this->mature === self::MATURE_CONTENT_NO) {
            return $this->images;
        }

        $assetId = app('asset')->findByName('mature_strict_image')?->file_id;

        return $assetId ? app('storage')->getUrls($assetId) : $this->images;
    }
}
