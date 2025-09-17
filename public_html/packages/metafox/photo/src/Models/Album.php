<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MetaFox\Photo\Contracts\HasTotalPhoto;
use MetaFox\Photo\Database\Factories\AlbumFactory;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Support\Facades\Album as FacadesAlbum;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class PhotoAlbum.
 * @mixin Builder
 * @property        int            $id
 * @property        int            $view_id
 * @property        string         $module_id
 * @property        int            $privacy
 * @property        int            $user_id
 * @property        string         $user_type
 * @property        int            $owner_id
 * @property        string         $owner_type
 * @property        string         $name
 * @property        int            $total_photo
 * @property        int            $total_item
 * @property        int            $total_video
 * @property        int            $total_comment
 * @property        int            $total_share
 * @property        int            $total_like
 * @property        int            $album_type
 * @property        int            $cover_photo_id
 * @property        int            $is_featured
 * @property        int            $is_approved
 * @property        int            $is_sponsor
 * @property        bool           $is_default
 * @property        int            $sponsor_in_feed
 * @property        string         $created_at
 * @property        string         $updated_at
 * @property        AlbumText|null $albumText
 * @property        Photo|null     $coverPhoto
 * @property        Collection     $items
 * @property        Collection     $groupedItems
 * @property        Collection     $ungroupedItems
 * @property        string         $album_link
 * @method   static AlbumFactory   factory()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Album extends Model implements
    Content,
    AppendPrivacyList,
    ActivityFeedSource,
    HasResourceStream,
    HasFeature,
    HasSponsor,
    HasApprove,
    HasPrivacy,
    HasTotalLike,
    HasTotalShare,
    HasTotalPhoto,
    HasTotalItem,
    HasTotalCommentWithReply,
    HasSavedItem,
    HasGlobalSearch
{
    use HasContent;
    use HasOwnerMorph;
    use HasUserMorph;
    use AppendPrivacyListTrait;
    use HasNestedAttributes;
    use HasFactory;
    use SoftDeletes;

    public const ENTITY_TYPE = 'photo_album';

    protected $table = 'photo_albums';

    /**
     * @var array<string, mixed>
     */
    public array $nestedAttributes = ['albumText' => ['text', 'text_parsed']];

    public const NORMAL_ALBUM   = 0;
    public const PROFILE_ALBUM  = 1;
    public const COVER_ALBUM    = 2;
    public const TIMELINE_ALBUM = 3;

    public const ALBUM_NAME = [
        self::TIMELINE_ALBUM => 'photo::phrase.time_line_photos',
        self::COVER_ALBUM    => 'photo::phrase.cover_photos',
        self::PROFILE_ALBUM  => 'photo::phrase.profile_photo',
    ];

    protected $fillable = [
        'view_id',
        'module_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'is_featured',
        'is_sponsor',
        'is_approved',
        'sponsor_in_feed',
        'name',
        'album_type',
        'total_photo',
        'total_item',
        'total_comment',
        'total_like',
        'total_share',
        'created_at',
        'updated_at',
        'cover_photo_id',
        'total_pending_comment',
        'total_pending_reply',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'album_link',
        'total_video',
        'owner_link',
        'owner_name',
    ];

    /**
     * @return HasOne
     */
    public function albumText(): HasOne
    {
        return $this->hasOne(AlbumText::class, 'id', 'id');
    }

    /**
     * @return HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'album_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(AlbumItem::class, 'album_id', 'id')
            ->orderByDesc('ordering')
            ->orderByDesc('id');
    }

    /**
     * @return BelongsTo
     */
    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'cover_photo_id', 'id');
    }

    protected static function newFactory(): AlbumFactory
    {
        return AlbumFactory::new();
    }

    /**
     * @return HasMany
     */
    public function privacyStreams(): HasMany
    {
        return $this->hasMany(AlbumPrivacyStream::class, 'item_id', 'id');
    }

    public function toActivityFeed(): ?FeedAction
    {
        return null;
    }

    public function toSavedItem(): array
    {
        $image = $this->images;

        return [
            'title'          => $this->toTitle(),
            'image'          => $image,
            'item_type_name' => __p("photo::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $image ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toTitle(): string
    {
        if (FacadesAlbum::isDefaultAlbum($this->album_type)) {
            return FacadesAlbum::getDefaultAlbumTitle($this);
        }

        $name = Arr::get($this->attributes, 'name', MetaFoxConstant::EMPTY_STRING);

        return ban_word()->clean($name);
    }

    public function getTotalVideoAttribute(): int
    {
        // work-around solution since adding total_video to photo_* tables is ambiguous
        // and might cause issue when toggling Video module
        return $this->total_item - $this->total_photo;
    }

    /**
     * @return ?array<mixed>
     */
    public function getImagesAttribute(): ?array
    {
        $images  = null;
        $photo   = $this->coverPhoto;
        $context = Auth::user() ?? UserFacade::getGuestUser();

        if ($photo !== null && $photo->isApproved()
            && policy_check(PhotoPolicy::class, 'view', $context, $photo)) {
            $images = $photo->images;
        }

        if (empty($images) && $this->total_item) {
            $approveItems = $this->approvedItems();
            if (!$context->hasPermissionTo('photo.moderate')) {
                $approveItems->join('photo_privacy_streams as stream', function (JoinClause $joinClause) use ($context) {
                    $joinClause->on('stream.item_id', '=', 'photo_album_item.item_id');
                });
                $approveItems->join('core_privacy_members as member', function (JoinClause $join) use ($context) {
                    $join->on('stream.privacy_id', '=', 'member.privacy_id')
                        ->where('member.user_id', '=', $context->entityId());
                });
            }
            $albumItem = $approveItems->where('item_type', Photo::ENTITY_TYPE)
                ->orderBy('photo_album_item.id', 'desc')->first();
            if ($albumItem instanceof AlbumItem) {
                $images = $albumItem->detail?->images;
            }
        }

        return $images;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl(sprintf('/%s/%s/%s', 'photo/album', $this->entityId(), $this->toSlug()));
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl(sprintf('/%s/%s/%s', 'photo/album', $this->entityId(), $this->toSlug()));
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('photo/album/' . $this->entityId());
    }

    public function groupedItems(): HasMany
    {
        return $this->hasMany(AlbumItem::class, 'album_id', 'id')->has('group');
    }

    public function ungroupedItems(): HasMany
    {
        return $this->hasMany(AlbumItem::class, 'album_id', 'id')->doesntHave('group');
    }

    public function approvedItems(): HasMany
    {
        return $this->hasMany(AlbumItem::class, 'album_id', 'id')
            ->where(function (Builder $query) {
                $query->whereHas('detail', function (Builder $subQuery) {
                    $subQuery->where('is_approved', 1);
                });
            });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toSearchable(): ?array
    {
        if ($this->album_type != self::NORMAL_ALBUM) {
            return null;
        }

        if ($this->items->count() <= 0) {
            return null;
        }

        $description = Arr::get($this->nestedAttributesFor, 'albumText.text', MetaFoxConstant::EMPTY_STRING);

        return [
            'title' => $this->toTitle() ?? MetaFoxConstant::EMPTY_STRING,
            'text'  => $description,
        ];
    }

    protected function isNormal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->album_type == self::NORMAL_ALBUM,
            set: fn () => ['album_type' => self::NORMAL_ALBUM]
        );
    }

    protected function isDefault(): Attribute
    {
        return Attribute::make(
            get: fn () => FacadesAlbum::isDefaultAlbum($this->album_type),
        );
    }

    protected function isTimeline(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->album_type == self::TIMELINE_ALBUM,
        );
    }

    public function getDescriptionAttribute(): ?string
    {
        // FOXSOCIAL5-6337
        $nested = $this->getNestedAttributesFor();

        if (!$nested) {
            return null;
        }

        return htmlspecialchars_decode($nested['albumText']['text'], ENT_QUOTES);
    }

    public function getAlbumLinkAttribute(): string
    {
        return $this->toLink() ?? '';
    }

    public function getOwnerLinkAttribute(): string
    {
        return $this->owner?->toLink() ?? '';
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->owner->name ?? '';
    }

    public function getNameAttribute(?string $name): ?string
    {
        if (null === $name) {
            return $name;
        }

        if (Arr::has(self::ALBUM_NAME, $this->album_type)) {
            $userName = $this->ownerEntity?->name;

            return __p(self::ALBUM_NAME[$this->album_type], ['full_name' => $userName]);
        }

        return $name;
    }

    /**
     * @param string     $resolution
     * @param string     $nameOrUrl
     * @param mixed|null $type
     * @param mixed|null $id
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildSeoData(string $resolution, string $nameOrUrl, mixed $type = null, mixed $id = null): array
    {
        return match ($nameOrUrl) {
            'photo.photo_album.edit', 'photo/album/{id}' => $this->getAlbumDetailSeoData($resolution),
            default => [],
        };
    }

    /**
     * @param string $resolution
     *
     * @return array<string, mixed>
     */
    public function getAlbumDetailSeoData(string $resolution): array
    {
        if ($resolution != 'web') {
            return [];
        }

        $owner = $this->owner;

        if (!$owner instanceof \MetaFox\Platform\Contracts\User) {
            return [];
        }

        if ($owner instanceof \MetaFox\User\Models\User) {
            return [];
        }

        $packageAlias = getAliasByEntityType($owner->entityType());
        $package      = app('core.packages')->getPackageByAlias($packageAlias);

        if (!$package) {
            return [];
        }
        $ownerLink = $owner->toLink();

        $breadcrumbs = [
            ['label' => $package->label, 'to' => $package->internal_url],
            ['label' => $owner->toTitle(), 'to' => $ownerLink],
            ['label' => __p('photo::phrase.label_menu_s'), 'to' => sprintf('%s/%s', $ownerLink, 'photo?stab=albums')],
        ];

        return [
            'breadcrumbs' => $breadcrumbs,
        ];
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
        if ($this->total_item <= 0) {
            return null;
        }

        return [
            'title' => __p('photo::phrase.sponsor_title_photo_album', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('albumText');
        $text = $this->albumText;

        if (!$text instanceof AlbumText) {
            return null;
        }

        return strip_tags($text->text_parsed);
    }

    public function toFeaturedData(): ?array
    {
        if ($this->total_item <= 0) {
            return null;
        }

        return [];
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->toTitle();
    }

    protected function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'name');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($title), language: null);
    }
}
