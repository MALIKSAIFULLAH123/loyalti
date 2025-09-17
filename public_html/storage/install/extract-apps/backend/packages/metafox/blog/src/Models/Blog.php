<?php

namespace MetaFox\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Blog\Database\Factories\BlogFactory;
use MetaFox\Blog\Notifications\BlogApproveNotification;
use MetaFox\Blog\Notifications\NewBlogToFollowerNotification;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceCategory;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasSponsorInFeed;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Blog.
 *
 * @mixin Builder
 * @property        int         $id
 * @property        string      $title
 * @property        string      $module_id
 * @property        int         $privacy
 * @property        int         $is_draft
 * @property        bool        $is_approved
 * @property        bool        $is_featured
 * @property        bool        $is_sponsor
 * @property        bool        $sponsor_in_feed
 * @property        int         $total_attachment
 * @property        int         $total_view
 * @property        string      $keywords
 * @property        string      $description
 * @property        int         $total_like
 * @property        int         $total_comment
 * @property        int         $total_tag_friend
 * @property        int         $total_share
 * @property        ?string[]   $tags
 * @property        ?int        $image_file_id
 * @property        string      $created_at
 * @property        string      $updated_at
 * @property        ?BlogText   $blogText
 * @property        Category    $categories
 * @property        Category    $activeCategories
 * @method   static BlogFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @todo TriTV: he class Blog has a coupling between objects value of 14. Consider to reduce the number of dependencies
 *       under 13. Please reduce this class.
 */
class Blog extends Model implements
    Content,
    ActivityFeedSource,
    AppendPrivacyList,
    HasPrivacy,
    HasResourceStream,
    HasResourceCategory,
    HasApprove,
    HasFeature,
    HasHashTag,
    HasSponsor,
    HasSponsorInFeed,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasTotalView,
    HasTotalAttachment,
    HasThumbnail,
    HasSavedItem,
    HasGlobalSearch
{
    use HasOwnerMorph;
    use HasUserMorph;
    use HasContent;
    use AppendPrivacyListTrait;
    use HasNestedAttributes;
    use HasFactory;
    use HasThumbnailTrait;
    use HasTotalAttachmentTrait;

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    public const ENTITY_TYPE = 'blog';

    public const BLOG_STATUS_PUBLIC = 1;
    public const BLOG_STATUS_DRAFT  = 2;

    /**
     * @var string[]
     */
    protected $casts = [
        'is_approved'     => 'boolean',
        'is_sponsor'      => 'boolean',
        'sponsor_in_feed' => 'boolean',
        'is_featured'     => 'boolean',
        'is_draft'        => 'boolean',
        'tags'            => 'array',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'categories',
        'blogText' => ['text', 'text_parsed'],
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'module_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'is_draft',
        'is_approved',
        'is_featured',
        'is_sponsor',
        'sponsor_in_feed',
        'tags',
        'updated_at',
        'created_at',
        'total_like',
        'total_share',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'total_pending_reply',
        'total_attachment',
        'image_file_id',
    ];

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'blog_tag_data',
            'item_id',
            'tag_id'
        )->using(BlogTagData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'blog_category_data',
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
            'blog_category_data',
            'item_id',
            'category_id'
        )->where('is_active', Category::IS_ACTIVE)->using(CategoryData::class);
    }

    /**
     * @return HasOne
     */
    public function blogText(): HasOne
    {
        return $this->hasOne(BlogText::class, 'id', 'id');
    }

    /**
     * @return FeedAction
     */
    public function toActivityFeed(): ?FeedAction
    {
        if ($this->isDraft()) {
            return null;
        }

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
            'privacy'    => $this->privacy,
        ]);
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PrivacyStream::class, 'item_id', 'id');
    }

    protected static function newFactory(): BlogFactory
    {
        return BlogFactory::new();
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => ban_word()->clean($this->title),
            'image'          => $this->images,
            'item_type_name' => __p("blog::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $this->getThumbnail() ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toSearchable(): ?array
    {
        // A draft blog is not allowed to be searched
        if ($this->isDraft()) {
            return null;
        }

        if (!$this->isApproved()) {
            return null;
        }

        $text = $this->blogText;

        return [
            'title' => $this->title,
            'text'  => $text ? $text->text_parsed : '',
        ];
    }

    public function getKeywordsAttribute()
    {
        return implode(', ', $this->tags ?? []);
    }

    public function getDescriptionAttribute()
    {
        return strip_tags(substr($this->blogText?->text_parsed, 0, 500));
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

    public function toApprovedNotification(): array
    {
        return [$this->user, new BlogApproveNotification($this)];
    }

    public function toSponsorData(): ?array
    {
        return [
            'title' => __p('blog::phrase.sponsor_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toFollowerNotification(): ?array
    {
        if ($this->isDraft()) {
            return null;
        }

        $message = __p('blog::phrase.user_create_new_blog_title', [
            'title'     => $this->toTitle(),
            'isTitle'   => (int) !empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewBlogToFollowerNotification();

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

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        return $this->description;
    }

    public function toFeaturedData(): ?array
    {
        return [];
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl(sprintf('/%s/%s/%s', $this->entityType(), $this->entityId(), $this->toSlug()));
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl(sprintf('/%s/%s/%s', $this->entityType(), $this->entityId(), $this->toSlug()));
    }

    protected function toSlug(): string
    {
        $title = Arr::get($this->attributes, 'title');

        if (null === $title) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($title), language: null);
    }
}
