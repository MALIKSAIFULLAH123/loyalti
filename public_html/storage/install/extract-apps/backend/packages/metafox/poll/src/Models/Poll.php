<?php

namespace MetaFox\Poll\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\ActivityFeedForm;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPendingMode;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasSponsorInFeed;
use MetaFox\Platform\Contracts\HasTaggedFriend;
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
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Poll\Database\Factories\PollFactory;
use MetaFox\Poll\Notifications\NewPollToFollowerNotification;
use MetaFox\Poll\Notifications\PollApproveNotification;
use MetaFox\Poll\Support\Facade\Poll as PollFacade;

/**
 * Class Poll.
 *
 * @property        int           $id
 * @property        string        $question
 * @property        string        $description
 * @property        string | null $caption
 * @property        int           $view_id
 * @property        int           $privacy
 * @property        bool          $is_featured
 * @property        bool          $is_sponsor
 * @property        bool          $sponsor_in_feed
 * @property        int           $total_view
 * @property        int           $total_like
 * @property        int           $total_comment
 * @property        int           $total_share
 * @property        int           $total_attachment
 * @property        int           $total_vote
 * @property        string        $image_file_id
 * @property        int           $randomize
 * @property        bool          $public_vote
 * @property        bool          $is_multiple
 * @property        bool          $is_closed
 * @property        Carbon        $closed_at
 * @property        string        $created_at
 * @property        string        $updated_at
 * @property        Collection    $answers
 * @property        int           $answers_count
 * @property        Collection    $results
 * @property        PollText|null $pollText
 * @property        Design        $design
 * @method   static PollFactory   factory(...$parameters)
 */
class Poll extends Model implements
    Content,
    ActivityFeedSource,
    ActivityFeedForm,
    AppendPrivacyList,
    HasPrivacy,
    HasResourceStream,
    HasApprove,
    HasFeature,
    HasSponsor,
    HasSponsorInFeed,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasTotalView,
    HasTotalAttachment,
    HasThumbnail,
    HasSavedItem,
    HasGlobalSearch,
    HasTaggedFriend,
    HasLocationCheckin,
    HasHashTag
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use AppendPrivacyListTrait;
    use HasNestedAttributes;
    use HasFactory;
    use HasThumbnailTrait;
    use HasTotalAttachmentTrait;
    use HasTaggedFriendTrait;

    public const ENTITY_TYPE = 'poll';

    public const FEED_POST_TYPE = self::ENTITY_TYPE;

    protected $table = 'polls';

    /**
     * @var string[]
     */
    protected $appends = ['image', 'is_closed'];

    /**
     * @var string[]
     */

    /**
     * @var array<string>|array<string, mixed>
     */
    protected array $nestedAttributes = [
        'design'   => ['percentage', 'background', 'border'],
        'pollText' => ['text', 'text_parsed'],
    ];

    protected $fillable = [
        'view_id',
        'privacy',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'question',
        'caption',
        'image_file_id',
        'randomize',
        'public_vote',
        'is_multiple',
        'closed_at',
        'is_approved',
        'is_featured',
        'is_sponsor',
        'sponsor_in_feed',
        'total_like',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'total_pending_reply',
        'total_tag_friend',
        'total_share',
        'total_view',
        'total_attachment',
        'total_vote',
        'updated_at',
        'created_at',
        'location_name',
        'location_latitude',
        'location_longitude',
        'pending_tagged_friends',
        'location_address',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_sponsor'             => 'boolean',
        'sponsor_in_feed'        => 'boolean',
        'is_featured'            => 'boolean',
        'public_vote'            => 'boolean',
        'is_multiple'            => 'boolean',
        'is_closed'              => 'boolean',
        'pending_tagged_friends' => 'array',
        'closed_at'              => 'datetime',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    public function toActivityFeed(): ?FeedAction
    {
        if (!$this->isApproved() && !$this->owner instanceof HasPendingMode) {
            return null;
        }

        if ($this->view_id === PollFacade::getIntegrationViewId()) {
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
            'content'    => $this->getFeedContent(),
        ]);
    }

    public function getIsClosedAttribute(): bool
    {
        return !empty($this->closed_at) && $this->closed_at->lessThan(now());
    }

    protected static function newFactory(): PollFactory
    {
        return PollFactory::new();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'poll_id', 'id');
    }

    public function design(): HasOne
    {
        return $this->hasOne(Design::class, 'id', 'id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'poll_id', 'id');
    }

    public function pollText(): HasOne
    {
        return $this->hasOne(PollText::class, 'id', 'id');
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PrivacyStream::class, 'item_id', 'id');
    }

    public function getFeedContent(): ?string
    {
        return $this->caption;
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => ban_word()->clean($this->question),
            'image'          => $this->images,
            'item_type_name' => __p("poll::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $this->getThumbnail() ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        if ($this->view_id === PollFacade::getIntegrationViewId()) {
            return null;
        }

        $text = $this->pollText;

        return [
            'title' => $this->question,
            'text'  => $text ? $text->text_parsed : '',
        ];
    }

    public function toTitle(): string
    {
        $title = $this->question ?? '';

        return empty($title) ? $title : ban_word()->clean($title);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->toTitle();
    }

    public function isIntegration(): bool
    {
        return $this->view_id === PollFacade::getIntegrationViewId();
    }

    /**
     * @return array<int, mixed>
     */
    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new PollApproveNotification($this)];
    }

    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'poll_tag_data',
            'item_id',
            'tag_id'
        )->using(PollTagData::class);
    }

    public function toFollowerNotification(): ?array
    {
        if ($this->view_id === PollFacade::getIntegrationViewId()) {
            return null;
        }

        $message = __p('poll::phrase.user_name_create_a_new_poll', [
            'title'     => $this->toTitle(),
            'isTitle'   => (int) !empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewPollToFollowerNotification();

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
            'title' => __p('poll::phrase.sponsor_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('pollText');

        $pollText = $this->pollText;
        if (!$pollText instanceof PollText) {
            return null;
        }

        return strip_tags($pollText->text_parsed);
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
        $question = Arr::get($this->attributes, 'question');

        if (null === $question) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::slug(ban_word()->clean($question), language: null);
    }
}
