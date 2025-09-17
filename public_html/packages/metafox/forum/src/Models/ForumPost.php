<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Forum\Database\Factories\ForumPostFactory;
use MetaFox\Forum\Notifications\ApprovedPost;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasFeed;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Platform\Contracts\User as ContractUser;

/**
 * Class ForumPost.
 *
 * @property        int              $id
 * @property        string           $title
 * @property        int              $owner_id
 * @property        int              $thread_id
 * @property        ?ForumPostText   $postText
 * @property        ?ForumThread     $thread
 * @property        string           $created_at
 * @property        string           $updated_at
 * @method   static ForumPostFactory factory(...$parameters)
 */
class ForumPost extends Model implements
    Content,
    ActivityFeedSource,
    HasTotalAttachment,
    AppendPrivacyList,
    HasApprove,
    HasTotalCommentWithReply,
    HasTotalLike,
    HasTotalShare,
    HasSavedItem
{
    use HasContent;
    use HasFactory;
    use HasNestedAttributes;
    use HasAmountsTrait;
    use HasTotalAttachmentTrait;
    use AppendPrivacyListTrait;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasTotalAttachmentTrait;
    use HasFeed;

    public const ENTITY_TYPE = 'forum_post';
    /**
     * @var array
     */
    public array $nestedAttributes = [
        'postText' => ['text', 'text_parsed'],
    ];
    /** @var string[] */
    protected $fillable = [
        'thread_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'is_approved',
        'total_attachment',
        'total_like',
        'total_share',
        'created_at',
        'updated_at',
    ];

    /**
     * @return ForumPostFactory
     */
    protected static function newFactory()
    {
        return ForumPostFactory::new();
    }

    /**
     * @return HasOne
     */
    public function postText(): HasOne
    {
        return $this->hasOne(ForumPostText::class, 'id');
    }

    /**
     * @return BelongsTo
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'thread_id');
    }

    public function quotePost(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'forum_post_quotes', 'post_id', 'quote_id');
    }

    public function quoteData(): HasOne
    {
        return $this->hasOne(ForumPostQuote::class, 'post_id');
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new ApprovedPost($this)];
    }

    public function toLink(): ?string
    {
        $id = $this->entityId();

        if (null === $id) {
            return null;
        }

        $thread = $this->thread;

        if (null == $thread) {
            return null;
        }

        return url_utility()->makeApiResourceUrl('forum/thread', $thread->entityId()) . '?' . http_build_query(['post_id' => $id]);
    }

    public function toRouter(): ?string
    {
        $id = $this->entityId();

        if (null === $id) {
            return null;
        }

        $thread = $this->thread;

        if (null == $thread) {
            return null;
        }

        return url_utility()->makeApiMobileResourceUrl('forum/thread', $thread->entityId()) . '?' . http_build_query(['post_id' => $id]);
    }

    public function toUrl(): ?string
    {
        $id = $this->entityId();

        if (null === $id) {
            return null;
        }

        $thread = $this->thread;

        if (null == $thread) {
            return null;
        }

        return url_utility()->makeApiResourceFullUrl('forum/thread', $thread->entityId()) . '?' . http_build_query(['post_id' => $id]);
    }

    public function toTitle(): string
    {
        return $this->postText->text_parsed;
    }

    public function isSaved(): bool
    {
        $context = user();

        return PolicyGate::check($this->entityType(), 'isSavedItem', [$context, $this]);
    }

    public function getTotalLike(): int
    {
        return $this->total_like;
    }

    public function getTotalAttachment(): int
    {
        return $this->total_attachment;
    }

    public function getTotalShare(): int
    {
        return $this->total_share;
    }

    public function getPrivacyAttribute(): int
    {
        return MetaFoxPrivacy::EVERYONE;
    }

    public function getQuotePost(): ?self
    {
        $quotePost = $this->quotePost;

        if (null !== $quotePost) {
            return $quotePost->first();
        }

        return null;
    }

    public function getThreadId(): int
    {
        return $this->thread_id;
    }

    public function getShortContentAttribute(): ?string
    {
        if (null !== $this->postText) {
            return parse_output()->getDescription($this->postText->text_parsed, 500);
        }

        $text = Arr::get($this->nestedAttributesFor, 'postText');

        if (is_array($text)) {
            return parse_output()->getDescription(Arr::get($text, 'text', MetaFoxConstant::EMPTY_STRING), 500);
        }

        return null;
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => $this->short_content ?? MetaFoxConstant::EMPTY_STRING,
            'image'          => null,
            'item_type_name' => __p("forum::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toActivityFeed(): ?FeedAction
    {
        $owner = $this->owner;

        if (!$owner->hasPendingMode()) {
            return null;
        }

        if (!$owner->isPendingMode()) {
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
            'status'     => MetaFoxConstant::ITEM_STATUS_PENDING,
        ]);
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('postText');
        $postText = $this->postText;

        if (!$postText instanceof ForumPostText) {
            return null;
        }

        return strip_tags($postText->text_parsed);
    }
}
