<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Forum\Database\Factories\ForumThreadFactory;
use MetaFox\Forum\Notifications\ApprovedThread;
use MetaFox\Forum\Notifications\NewThreadToFollowerNotification;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class ForumThread.
 *
 * @property        int                       $id
 * @property        mixed                     $title
 * @property        string|null               $image_path
 * @property        string                    $server_id
 * @property        Forum                     $forum
 * @property        bool                      $is_wiki
 * @property        ForumPost                 $posts
 * @property        mixed                     $short_description
 * @property        mixed                     $description
 * @property        bool                      $is_closed
 * @property        int                       $forum_id
 * @property        ForumThreadSubscribe|null $subscribed
 * @method   static ForumThreadFactory        factory(...$parameters)
 * @method          mixed                     incrementOrDecrement($column, $amount, $extra, $method)
 */
class ForumThread extends Model implements
    Content,
    HasHashTag,
    HasTotalView,
    HasApprove,
    HasSavedItem,
    HasThumbnail,
    HasTotalAttachment,
    HasTotalLike,
    ActivityFeedSource,
    HasTotalShare,
    HasSponsor,
    HasGlobalSearch
{
    use HasFactory;
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasAmountsTrait;
    use HasThumbnailTrait;
    use HasItemMorph;
    use HasNestedAttributes;
    use HasTotalAttachmentTrait;
    use HasEntity;

    public const ENTITY_TYPE = 'forum_thread';

    /**
     * @var array
     */
    public array $nestedAttributes = [
        'description' => ['text', 'text_parsed'],
    ];

    /** @var string[] */
    protected $fillable = [
        'title',
        'forum_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'item_type',
        'item_id',
        'is_wiki',
        'tags',
        'is_approved',
        'is_sticked',
        'is_closed',
        'is_sponsor',
        'sponsor_in_feed',
        'total_attachment',
        'total_comment',
        'total_view',
        'total_like',
        'total_share',
        'first_post_id',
        'last_post_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tags'             => 'array',
        'is_sponsor'       => 'boolean',
        'total_comment'    => 'integer',
        'total_view'       => 'integer',
        'total_like'       => 'integer',
        'total_share'      => 'integer',
        'total_attachment' => 'integer',
        'first_post_id'    => 'integer',
        'last_post_id'     => 'integer',
        'is_closed'        => 'boolean',
        'is_wiki'          => 'boolean',
    ];

    /**
     * @return ForumThreadFactory
     */
    protected static function newFactory()
    {
        return ForumThreadFactory::new();
    }

    public function description(): HasOne
    {
        return $this->hasOne(ForumThreadText::class, 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'forum_thread_tag_data', 'item_id', 'tag_id')
            ->using(ForumThreadTagData::class);
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id')
            ->withTrashed();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'thread_id');
    }

    public function lastReads(): HasMany
    {
        return $this->hasMany(ForumThreadLastRead::class, 'thread_id');
    }

    public function hasRead(): HasOne
    {
        $context = user();

        return $this->hasOne(ForumThreadLastRead::class, 'thread_id')
            ->where([
                'user_type' => $context->entityType(),
                'user_id'   => $context->entityId(),
            ]);
    }

    public function subscribes(): HasMany
    {
        return $this->hasMany(ForumThreadSubscribe::class, 'item_id');
    }

    public function subscribed(): HasOne
    {
        $context = user();

        return $this->hasOne(ForumThreadSubscribe::class, 'item_id')
            ->where([
                'forum_thread_subscribes.user_type' => $context->entityType(),
                'forum_thread_subscribes.user_id'   => $context->entityId(),
            ]);
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new ApprovedThread($this)];
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

    public function getTotalPost(): int
    {
        return Arr::get($this->attributes, 'total_comment', 0);
    }

    public function getTotalView(): int
    {
        return Arr::get($this->attributes, 'total_view', 0);
    }

    public function getTags(): array
    {
        if (null === $this->tags) {
            return [];
        }

        return $this->tags;
    }

    public function getPrivacyAttribute(): ?int
    {
        $owner = $this->owner;

        if ($owner instanceof HasPrivacy) {
            return (int) $owner->privacy;
        }

        if (Auth::guest()) {
            return MetaFoxPrivacy::EVERYONE;
        }

        if (!PrivacyPolicy::checkPermissionOwner(user(), $owner)) {
            return null;
        }

        return MetaFoxPrivacy::EVERYONE;
    }

    public function isClosed(): bool
    {
        return (bool) Arr::get($this->attributes, 'is_closed', false);
    }

    public function isSubscribed(): bool
    {
        return null !== $this->subscribed;
    }

    public function isViewed(): bool
    {
        return null !== $this->hasRead;
    }

    public function isWiki(): bool
    {
        return (bool) Arr::get($this->attributes, 'is_wiki', false);
    }

    public function getCreatedAt(): string
    {
        return Arr::get($this->attributes, 'created_at', MetaFoxConstant::EMPTY_STRING);
    }

    public function getUpdatedAt(): string
    {
        return Arr::get($this->attributes, 'updated_at', MetaFoxConstant::EMPTY_STRING);
    }

    /**
     * @return int[]
     */
    public function getSizes(): array
    {
        // TODO: Implement getSizes() method.
        return [];
    }

    /**
     * @return string|null
     */
    public function getImageAttribute(): ?string
    {
        return null;
    }

    /**
     * @return mixed[]|null
     */
    public function getImagesAttribute(): ?array
    {
        return [];
    }

    /**
     * @return mixed[]
     */
    public function toSavedItem(): array
    {
        return [
            'title'          => $this->toTitle(),
            'image'          => !empty($this->images) ? $this->images : null,
            'item_type_name' => __p("forum::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $this->getThumbnail() ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("forum/thread/{$this->entityId()}/{$this->toSlug()}");
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileResourceUrl('forum/thread', $this->entityId());
    }

    /**
     * @return int
     */
    public function getTotalLike(): int
    {
        return Arr::get($this->attributes, 'total_like', 0);
    }

    /**
     * @return int
     */
    public function getTotalShare(): int
    {
        return Arr::get($this->attributes, 'total_share', 0);
    }

    public function getItem(): ?Entity
    {
        $itemType = $this->item_type;

        if (null !== $itemType) {
            return $this->item;
        }

        return null;
    }

    public function getAttachments(): ?Collection
    {
        return $this->attachments;
    }

    public function getForumId(): int
    {
        return Arr::get($this->attributes, 'forum_id', 0);
    }

    public function setForumId(int $id): void
    {
        $this->forum_id = $id;
    }

    public function isSaved(): bool
    {
        $context = user();

        return PolicyGate::check($this->entityType(), 'isSavedItem', [$context, $this]);
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("forum/thread/{$this->entityId()}/{$this->toSlug()}");
    }

    public function isSticked(): bool
    {
        return (bool) Arr::get($this->attributes, 'is_sticked', false);
    }

    public function isSponsor(): bool
    {
        return (bool) Arr::get($this->attributes, 'is_sponsor', false);
    }

    public function toActivityFeed(): ?FeedAction
    {
        if (!$this->isApproved() && !$this->owner instanceof HasPendingMode) {
            return null;
        }

        if (null === $this->user) {
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
            'privacy'    => $this->getPrivacy(),
        ]);
    }

    public function getPrivacy(): int
    {
        return MetaFoxPrivacy::EVERYONE;
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $text = $this->description;

        if (null == $text) {
            return null;
        }

        return [
            'title' => $this->title,
            'text'  => $text->text_parsed,
        ];
    }

    public function getShortDescriptionAttribute(): ?string
    {
        $description = $this->description;

        if (null === $description) {
            return null;
        }

        $text = parse_output()->getDescription($description->text_parsed, 500);

        return $text;
    }

    public function firstPost(): HasOne
    {
        return $this->hasOne(ForumPost::class, 'thread_id')
            ->where('forum_posts.is_approved', '=', 1)
            ->orderBy('forum_posts.created_at')
            ->orderBy('forum_posts.id')
            ->limit(1);
    }

    public function lastPost(): HasOne
    {
        return $this->hasOne(ForumPost::class, 'thread_id')
            ->where('forum_posts.is_approved', '=', 1)
            ->orderByDesc('forum_posts.created_at')
            ->orderByDesc('forum_posts.id')
            ->limit(1);
    }

    public function firstListingPost(): HasOne
    {
        return $this->hasOne(ForumPost::class, 'id', 'first_post_id');
    }

    public function lastListingPost(): HasOne
    {
        return $this->hasOne(ForumPost::class, 'id', 'last_post_id');
    }

    protected function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'title');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($title), language: null);
    }

    public function buildSeoData(string $resolution, string $nameOrUrl): array
    {
        if ($nameOrUrl != 'forum/thread/{id}/{slug?}') {
            return [];
        }
        $breadcrumbs = [];

        if ($this->forum instanceof Forum) {
            $breadcrumbs = array_merge($breadcrumbs, Arr::get($this->forum->buildSeoData($resolution), 'breadcrumbs', []));

            $breadcrumbs[] = [
                'label' => $this->forum->toTitle(),
                'to'    => $this->forum->toLink(),
            ];
        }

        if ($this->isWiki()) {
            $breadcrumbs = Arr::get($this->buildWikiSeoData(), 'breadcrumbs');
        }

        return [
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    public function buildWikiSeoData(): array
    {
        return [
            'breadcrumbs' => [
                ['label' => __p('forum::web.forums'), 'to' => '/forum'],
                ['label' => __p('forum::web.wiki'), 'to' => '/forum/wiki-thread'],
            ],
        ];
    }

    public function toFollowerNotification(): ?array
    {
        $message = __p('forum::notification.user_name_created_new_a_thread_title', [
            'title'     => $this->toTitle(),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewThreadToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
            'type'    => $notification->getType(),
        ];
    }

    public function toSponsorData(): ?array
    {
        return [
            'title' => __p('forum::phrase.sponsor_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('description');
        $description = $this->description;

        if (!$description instanceof ForumThreadText) {
            return null;
        }

        return strip_tags($description->text_parsed);
    }

    public function getUnsupportChangeFeedPrivacyAttribute(): bool
    {
        return true;
    }

    public function getActionShareOnFeedAttribute(): bool
    {
        return true;
    }
}
