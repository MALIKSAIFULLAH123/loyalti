<?php

namespace MetaFox\Activity\Models;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Database\Factories\FeedFactory;
use MetaFox\Activity\Notifications\ApproveFeedNotification;
use MetaFox\Activity\Notifications\PendingFeedNotification;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasItemMorph as HasItemContract;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\PostAs;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasHashTagTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as FacadesUser;

/**
 * Class Feed.
 *
 * @mixin Builder
 * @property int                $id
 * @property int                $privacy
 * @property int                $privacy_id
 * @property string             $type_id            - action type in activity_types
 * @property int                $feed_reference
 * @property int                $parent_feed_id
 * @property int                $parent_module_id
 * @property string|null        $content
 * @property int                $total_view
 * @property int                $total_share
 * @property string|Carbon      $created_at
 * @property string|Carbon      $updated_at
 * @property int                $is_hide            - not autoload.
 * @property int                $is_approved
 * @property string             $status
 * @property Stream[]|null      $stream
 * @property string             $from_resource
 * @property string|null|Carbon $latest_activity_at
 */
class Feed extends Model implements
    Content,
    HasPrivacy,
    HasTotalView,
    HasTotalShare,
    HasTotalLike,
    HasTotalCommentWithReply,
    HasItemContract,
    HasSponsor,
    HasHashTag,
    HasGlobalSearch,
    HasSavedItem
{
    use HasContent {
        enableSponsor as enableContentSponsor;
        disableSponsor as disableContentSponsor;
    }
    use HasAmountsTrait;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasItemMorph;
    use HasFactory;
    use HasHashTagTrait;
    use AppendPrivacyListTrait;

    public const ENTITY_TYPE = 'feed';

    public const IMPORT_ENTITY_TYPE = 'activity_feed';

    public const FROM_FEED_RESOURCE = 'feed';

    public const FROM_APP_RESOURCE = 'app';

    public const TO_LINK_REVIEW  = '/settings/review';
    public const TO_ROUTE_REVIEW = '/settings/reviewPosts/review';
    protected $table    = 'activity_feeds';
    protected $fillable = [
        'privacy',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'item_id',
        'item_type',
        'type_id',
        'feed_reference',
        'parent_feed_id',
        'parent_module_id',
        'content',
        'total_like',
        'total_comment',
        'total_reply',
        'total_view',
        'total_share',
        'is_sponsor',
        'updated_at',
        'created_at',
        'status',
        'from_resource',
        'latest_activity_at',
    ];

    protected $casts = [
        'is_sponsor' => 'boolean',
    ];

    /**
     * @var string[]
     */
    protected $appends = [];

    protected static function newFactory(): FeedFactory
    {
        return FeedFactory::new();
    }

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'activity_tag_data',
            'item_id',
            'tag_id'
        )->using(ActivityTagData::class);
    }

    public function stream(): HasMany
    {
        return $this->hasMany(Stream::class, 'feed_id', 'id');
    }

    public function pinnedFeeds(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'activity_pins', 'feed_id')
            ->withTimestamps();
    }

    public function getIsHideAttribute(): bool
    {
        return $this->hiddenFeeds()
            ->where('user_id', '=', Auth::id())
            ->exists();
    }

    public function hiddenFeeds(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'activity_hidden', 'feed_id')
            ->withTimestamps();
    }

    public function history(): HasMany
    {
        return $this->hasMany(ActivityHistory::class, 'feed_id', 'id');
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $content = $this->content;

        if (null !== $content) {
            app('events')->dispatch('core.strip_content', [$this, &$content]);
        }

        return [
            'title' => $content,
            'text'  => $content,
        ];
    }

    public function toTitle(): string
    {
        $content = $this->content;

        if ($content) {
            $attributeParser = [
                'parse_url' => false,
            ];

            app('events')->dispatch('core.parse_content', [$this, &$content, $attributeParser]);

            return ban_word()->clean($content);
        }

        if ($this->item instanceof Content) {
            return ban_word()->clean($this->item->toTitle());
        }

        return '';
    }

    /**
     * @return ?array<mixed>
     */
    public function toPendingNotification(): ?array
    {
        $owner = $this->owner;

        if ($owner instanceof HasPendingMode) {
            $notifiables = [$owner->user];

            if (method_exists($owner, 'toPendingNotifiables')) {
                $notifiables = $owner->toPendingNotifiables($this->user);
            }

            if (!is_array($notifiables)) {
                return null;
            }

            if (!count($notifiables)) {
                return null;
            }

            return [$notifiables, new PendingFeedNotification($this)];
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    public function toApprovedNotification(): array
    {
        return [$this->user, new ApproveFeedNotification($this)];
    }

    public function isApproved(): bool
    {
        return $this->status == MetaFoxConstant::ITEM_STATUS_APPROVED;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->isApproved();
    }

    public function setIsApprovedAttribute(bool $value): void
    {
        $this->status = $value ? MetaFoxConstant::ITEM_STATUS_APPROVED : MetaFoxConstant::ITEM_STATUS_PENDING;
    }

    protected function isPending(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status == MetaFoxConstant::ITEM_STATUS_PENDING,
            set: fn () => ['status' => MetaFoxConstant::ITEM_STATUS_PENDING],
        );
    }

    protected function isRemoved(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status == MetaFoxConstant::ITEM_STATUS_REMOVED,
            set: fn () => ['status' => MetaFoxConstant::ITEM_STATUS_REMOVED],
        );
    }

    protected function isDenied(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status == MetaFoxConstant::ITEM_STATUS_DENIED,
            set: fn () => ['status' => MetaFoxConstant::ITEM_STATUS_DENIED],
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function toLink(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        $toLinkPending = $this->toPendingPreview();
        if ($toLinkPending) {
            return $toLinkPending;
        }

        $item = $this->item;

        if ($item instanceof HasUrl) {
            if (resolve(TypeManager::class)->hasFeature($this->type_id, Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
                return $item->toLink();
            }
        }

        $link = url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());

        $owner = $this->owner;

        if (!$owner instanceof PostBy) {
            return $link;
        }

        if (!$owner->hasFeedDetailPage()) {
            return $link;
        }

        $ownerLink = $owner->toLink();

        if (!$ownerLink) {
            return $link;
        }

        $link = rtrim($ownerLink, '/') . '/' . ltrim($link, '/');

        return $link;
    }

    /**
     * @throws AuthenticationException
     */
    public function toRouter(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        $toLinkPending = $this->toPendingPreview();
        if ($toLinkPending) {
            return $toLinkPending;
        }

        $item = $this->item;

        if ($item instanceof HasUrl) {
            if (resolve(TypeManager::class)->hasFeature($this->type_id, Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
                return $item->toRouter();
            }
        }

        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    /**
     * @throws AuthenticationException
     */
    public function toUrl(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        $toLinkPending = $this->toPendingPreview();
        if ($toLinkPending) {
            return url_utility()->makeApiFullUrl($toLinkPending);
        }

        $item = $this->item;

        if ($item instanceof HasUrl) {
            if (resolve(TypeManager::class)->hasFeature($this->type_id, Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
                return $item->toUrl();
            }
        }

        $link = $this->toLink();

        return url_utility()->makeApiFullUrl($link);
    }

    /**
     * @return string|null
     * @throws AuthenticationException
     */
    protected function toPendingPreview(): ?string
    {
        /** @var Stream $stream */
        $stream = LoadReduce::remember(
            sprintf('feed::toPendingPreview(%s,%s)', $this->entityType(), $this->entityId()),
            fn () => $this->stream()->first()
        );

        if ($stream?->status == Stream::STATUS_ALLOW) {
            return MetaFox::isMobile() ? self::TO_ROUTE_REVIEW : self::TO_LINK_REVIEW;
        }

        return null;
    }

    public function getOwnerPendingMessage(): ?string
    {
        if (null === $this->owner) {
            return null;
        }

        $pendingMessage = $this->owner->getPendingMessage();

        if (null !== $pendingMessage) {
            return $pendingMessage;
        }

        return __p('core::phrase.thanks_for_your_item_for_approval');
    }

    public function toSavedItem(): array
    {
        if ($this->item instanceof HasSavedItem) {
            return $this->item->toSavedItem();
        }

        return [];
    }

    public function streamPending(): bool
    {
        $owner = $this->owner;

        return $this->stream()->where('owner_id', $owner->entityId())
            ->where('status', Stream::STATUS_ALLOW)->exists();
    }

    public function pinned(): HasMany
    {
        return $this->hasMany(Pin::class, 'feed_id');
    }

    public function toDetailLink(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        $toLinkPending = $this->toPendingPreview();

        if ($toLinkPending) {
            return $toLinkPending;
        }

        return url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
    }

    public function toDetailUrl(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        $toLinkPending = $this->toPendingPreview();

        if ($toLinkPending) {
            return $toLinkPending;
        }

        $link = $this->toLink();

        return url_utility()->makeApiFullUrl($link);
    }

    /**
     * @param string $resolution
     * @param string $nameOrUrl
     * @param string $type
     * @param int    $id
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildSeoData($resolution, $nameOrUrl, $type, $id): array
    {
        $siteTitle = Settings::get('core.general.site_title');
        $rawTitle  = $this->toTitle() ?? '';
        $title     = strip_tag_content($rawTitle);
        $title     = parse_output()->limit($title, MetaFoxConstant::DEFAULT_MAX_SEO_TITLE_LENGTH);

        return [
            'title'    => $this->seo_title,
            'og:title' => $title ? $title : $siteTitle,
        ];
    }

    public function getSeoTitleAttribute(): string
    {
        $siteName  = Settings::get('core.general.site_name');
        $siteTitle = Settings::get('core.general.site_title');
        $delimiter = Settings::get('core.general.title_delim');
        $rawTitle  = $this->toTitle() ?? '';
        $title     = strip_tag_content($rawTitle);
        $fullName  = $this->user?->full_name ?? __p('core::phrase.deleted_user');

        if ($this->user instanceof PostAs) {
            $fullName = $this->userEntity->name;
        }

        $title = parse_output()->limit($title, MetaFoxConstant::DEFAULT_MAX_SEO_TITLE_LENGTH);

        return $title ? sprintf('%s - %s %s %s', $fullName, $title, $delimiter, $siteName) : $siteTitle;
    }

    public function toSponsorData(): ?array
    {
        $title = null;

        if ($this->item instanceof HasTitle) {
            $title = $this->item->toTitle();
        }

        if ($this->item instanceof Content) {
            $sponsorData = $this->item->toSponsorData();

            if (is_array($sponsorData) && is_string(Arr::get($sponsorData, 'title'))) {
                $title = Arr::get($sponsorData, 'title');
            }
        }

        return [
            'title' => $title ?? __p('activity::phrase.sponsor_feed'),
        ];
    }

    public function enableSponsor(): void
    {
        $this->enableContentSponsor();

        if ($this->item instanceof Content) {
            $this->item->enableFeedSponsor();
        }
    }

    public function disableSponsor(): void
    {
        $this->disableContentSponsor();

        if ($this->item instanceof Content) {
            $this->item->disableFeedSponsor();
        }
    }

    public function toSitemapUrl(): ?string
    {
        if (!$this->isApproved()) {
            return null;
        }

        if ($this->toPendingPreview()) {
            return null;
        }

        $link  = url_utility()->makeApiResourceUrl($this->entityType(), $this->entityId());
        $url   = url_utility()->makeApiFullUrl($link);
        $owner = $this->owner;

        if (!$owner instanceof PostBy) {
            return $url;
        }

        if (!$owner->hasFeedDetailPage()) {
            return $url;
        }

        $guest = FacadesUser::getGuestUser();
        if (!$guest instanceof ContractUser) {
            return null;
        }

        if (!$owner->checkContentShareable($guest)) {
            return null;
        }

        $ownerLink = $owner->toLink();

        if (!$ownerLink) {
            return $url;
        }

        return url_utility()->makeApiFullUrl(rtrim($ownerLink, '/') . '/' . ltrim($link, '/'));
    }

    public function getKeepCommentItemRouterAttribute(): bool
    {
        return true;
    }

    /**
     * @return array<int, mixed>
     */
    public function toOGImages(): array
    {
        $this->loadMissing('item');
        if (null === $this->item) {
            return [];
        }

        if (!method_exists($this->item, 'toFeedOGImages')) {
            return [];
        }

        $images = $this->item->toFeedOGImages();

        if (!is_array($images)) {
            return [];
        }

        return $images;
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        if (!empty($this->content)) {
            return ban_word()->clean($this->content);
        }

        $this->loadMissing('item');
        $item = $this->item;

        if (is_object($item) && method_exists($item, 'toOGDescription')) {
            return $item->toOGDescription($context);
        }

        return null;
    }

    public function reactItem()
    {
        $result = resolve(TypeManager::class)->hasFeature($this->type_id, Type::ACTION_ON_FEED_TYPE)
            ? $this
            : $this->item;

        return $result ?? $this;
    }

    public function toFollowerCallbackMessage(?string $locale = null): ?string
    {
        if (method_exists($this->item, 'toFollowerCallbackMessage') && $this->item->toFollowerCallbackMessage($locale)) {
            return $this->item->toFollowerCallbackMessage($locale);
        }

        return null;
    }

    public function toReportTitle(): string
    {
        $itemTitle = $this->toTitle();

        if (empty($itemTitle) && $this->item instanceof Post) {
            $itemTitle = $this->item->location_address;
        }

        if (empty($itemTitle)) {
            $itemTitle = __p('activity::phrase.no_content');
        }

        return $itemTitle;
    }
}
