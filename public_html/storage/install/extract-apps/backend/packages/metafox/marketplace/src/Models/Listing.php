<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Marketplace\Database\Factories\ListingFactory;
use MetaFox\Marketplace\Notifications\ListingApprovedNotification;
use MetaFox\Marketplace\Notifications\NewListingToFollowerNotification;
use MetaFox\Marketplace\Support\Facade\Listing as Facade;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceCategory;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasHashTagTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Listing.
 *
 * @property        int            $id
 * @property        int            $is_approved
 * @property        int            $privacy
 * @property        int            $user_id
 * @property        string         $user_type
 * @property        int            $owner_idf
 * @property        string         $owner_type
 * @property        int            $is_featured
 * @property        int            $is_sponsor
 * @property        int            $sponsor_in_feed
 * @property        int            $total_comment
 * @property        int            $total_like
 * @property        int            $total_share
 * @property        int            $total_view
 * @property        int            $total_attachment
 * @property        bool           $allow_payment
 * @property        bool           $allow_point_payment
 * @property        bool           $auto_sold
 * @property        bool           $is_sold
 * @property        bool           $is_expired
 * @property        int            $is_notified
 * @property        string         $currency
 * @property        array          $price
 * @property        string         $title
 * @property        string         $short_description
 * @property        string         $image_file_id
 * @property        string         $server_id
 * @property        string         $location_name
 * @property        string         $location_latitude
 * @property        string         $location_longitude
 * @property        string         $country_iso
 * @property        int            $postal_code
 * @property        string         $city
 * @property        string         $created_at
 * @property        string         $updated_at
 * @property        string         $external_link
 * @property        Text|null      $marketplaceText
 * @property        Collection     $categories
 * @property        Collection     $activeCategories
 * @property        Collection     $invites
 * @property        Collection     $photos
 * @property        Collection     $priceItems
 * @method   static ListingFactory factory()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @mixin Builder
 */
class Listing extends Model implements
    Content,
    ActivityFeedSource,
    AppendPrivacyList,
    HasPrivacy,
    HasResourceStream,
    HasResourceCategory,
    HasFeature,
    HasSponsor,
    HasLocationCheckin,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasTotalView,
    HasTotalAttachment,
    HasThumbnail,
    HasSavedItem,
    HasHashTag,
    HasGlobalSearch,
    HasApprove
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use AppendPrivacyListTrait;
    use HasNestedAttributes;
    use HasFactory;
    use HasThumbnailTrait;
    use HasHashTagTrait;
    use HasTotalAttachmentTrait;
    use SoftDeletes;

    public const ENTITY_TYPE = 'marketplace';

    protected $table = 'marketplace_listings';

    /**
     * @var string[]
     */
    protected $casts = [
        'is_sponsor'          => 'boolean',
        'sponsor_in_feed'     => 'boolean',
        'is_featured'         => 'boolean',
        'is_sold'             => 'boolean',
        'allow_payment'       => 'boolean',
        'auto_sold'           => 'boolean',
        'allow_point_payment' => 'boolean',
        'tags'                => 'array',
        'location_latitude'   => 'float',
        'location_longitude'  => 'float',
        'start_expired_at'    => 'integer',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'categories',
        'marketplaceText' => ['text', 'text_parsed'],
    ];

    protected $fillable = [
        'is_approved',
        'privacy',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'is_featured',
        'is_sponsor',
        'sponsor_in_feed',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'total_pending_reply',
        'total_like',
        'total_share',
        'total_view',
        'total_attachment',
        'allow_payment',
        'allow_point_payment',
        'auto_sold',
        'is_sold',
        'is_notified',
        'currency',
        'price',
        'title',
        'short_description',
        'image_file_id',
        'location_latitude',
        'location_longitude',
        'location_name',
        'country_iso',
        'postal_code',
        'city',
        'tags',
        'start_expired_at',
        'notify_at',
        'external_link',
        'created_at',
        'updated_at',
        'location_address',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'marketplace_listing_tag_data',
            'item_id',
            'tag_id'
        )->using(ListingTagData::class);
    }

    protected static function newFactory(): ListingFactory
    {
        return ListingFactory::new();
    }

    public function toActivityFeed(): ?FeedAction
    {
        if (null === $this->user) {
            return null;
        }

        if (!$this->is_approved && !$this->owner instanceof HasPendingMode) {
            return null;
        }

        return new FeedAction([
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->ownerId(),
            'owner_type' => $this->ownerType(),
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'type_id'    => $this->entityType(),
            'privacy'    => $this->privacy,
        ]);
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PrivacyStream::class, 'item_id', 'id');
    }

    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, $this->country_iso, $this->location_address];
    }

    public function isShowLocation(): bool
    {
        return false;
    }

    /**
     * toLocationObject.
     *
     * @return array<mixed>
     */
    public function toLocationObject(): array
    {
        return [
            'address'    => $this->location_name,
            'lat'        => $this->location_latitude,
            'lng'        => $this->location_longitude,
            'short_name' => $this->country_iso,
            'full_address' => $this->location_address,
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'marketplace_category_data',
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
            'marketplace_category_data',
            'item_id',
            'category_id'
        )->where('is_active', Category::IS_ACTIVE)->using(CategoryData::class);
    }

    /**
     * @return HasOne
     */
    public function marketplaceText(): HasOne
    {
        return $this->hasOne(Text::class, 'id', 'id');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class, 'listing_id', 'id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Image::class, 'listing_id', 'id')
            ->orderBy('ordering');
    }

    /**
     * toSavedItem.
     *
     * @return array<mixed>
     */
    public function toSavedItem(): array
    {
        return [
            'title'          => $this->toTitle(),
            'image'          => $this->images,
            'item_type_name' => __p("marketplace::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $this->getThumbnail() ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    /**
     * toSearchable.
     *
     * @return ?array<mixed>
     */
    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $text = MetaFoxConstant::EMPTY_STRING;

        if (null !== $this->marketplaceText) {
            $text = $this->marketplaceText->text_parsed;
        }

        return [
            'title' => $this->title,
            'text'  => $text,
        ];
    }

    public function toTitle(): string
    {
        $title = Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);

        return ban_word()->clean($title);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->toTitle();
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl(sprintf('/%s/%s/%s', 'marketplace', $this->entityId(), $this->toSlug()));
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl(sprintf('/%s/%s/%s', 'marketplace', $this->entityId(), $this->toSlug()));
    }

    protected function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'title');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($title), language: null);
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl('marketplace/' . $this->entityId());
    }

    /**
     * getPriceAttribute.
     *
     * @return array<mixed>
     */
    public function getPriceAttribute(): array
    {
        $prices = Arr::get($this->attributes, 'price');

        if (null === $prices) {
            return [];
        }

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (!is_array($prices)) {
            return [];
        }

        foreach ($prices as $key => $value) {
            if (!is_numeric($value)) {
                continue;
            }

            $prices[$key] = round($value, 2);
        }

        return $prices;
    }

    /**
     * toApprovedNotification.
     *
     * @return array<mixed>
     */
    public function toApprovedNotification(): array
    {
        return [$this->user, new ListingApprovedNotification($this)];
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ListingHistory::class, 'listing_id', 'id');
    }

    public function getIsExpiredAttribute(): bool
    {
        return Facade::isExpired($this);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'listing_id', 'id');
    }

    public function hasSoldInvoice(User $context): bool
    {
        if ($this->userId() != $context->entityId()) {
            return false;
        }

        return (bool) $this->invoices()->count();
    }

    public function paidInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'listing_id', 'id')
            ->whereIn('status', [Facade::getPendingPaymentStatus(), Facade::getCompletedPaymentStatus()]);
    }

    public function pendingInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'listing_id', 'id')
            ->whereIn('status', [Facade::getInitPaymentStatus()]);
    }

    public function getImagesAttribute(): ?array
    {
        $thumbnail = $this->photos->first()?->image_file_id;

        if ($thumbnail == null) {
            return null;
        }

        return app('storage')->getUrls($thumbnail);
    }

    public function priceItems(): HasMany
    {
        return $this->hasMany(ListingPrice::class, 'listing_id');
    }

    public function toFollowerNotification(): ?array
    {
        $message = __p('marketplace::phrase.user_name_create_a_new_listing', [
            'title'     => $this->toTitle(),
            'isTitle'   => (int) !empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewListingToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
            'type'    => $notification->getType(),
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
        return [
            'title' => __p('marketplace::phrase.sponsor_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toOGDescription(?User $context = null): ?string
    {
        $this->loadMissing('marketplaceText');
        $text = $this->marketplaceText;

        if (!$text instanceof Text) {
            return null;
        }

        return strip_tags($text->text_parsed);
    }

    public function toFeaturedData(): ?array
    {
        return [];
    }
}
