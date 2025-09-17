<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\LiveStreaming\Notifications\LiveVideoApproveNotification;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\LiveStreaming\Database\Factories\LiveVideoFactory;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\NotificationSettingRepositoryInterface;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
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
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;
use MetaFox\Platform\Contracts\User as ContractUser;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class LiveVideo.
 * @mixin Builder
 * @property        int              $id
 * @property        string           $title
 * @property        string           $module_id
 * @property        int              $privacy
 * @property        bool             $is_approved
 * @property        bool             $is_featured
 * @property        bool             $is_landscape
 * @property        bool             $is_sponsor
 * @property        bool             $sponsor_in_feed
 * @property        int              $total_attachment
 * @property        int              $total_view
 * @property        int              $total_like
 * @property        int              $total_comment
 * @property        int              $total_share
 * @property        ?string[]        $tags
 * @property        ?int             $image_file_id
 * @property        string           $created_at
 * @property        string           $updated_at
 * @property        ?LiveVideoText   $liveVideoText
 * @property        string           $stream_key
 * @property        string           $duration
 * @property        string           $asset_id
 * @property        string           $live_stream_id
 * @property        string           $live_type
 * @property        string           $location_latitude
 * @property        string           $location_longitude
 * @property        string           $location_name
 * @property        int              $is_streaming
 * @property        int              $view_id
 * @property        int              $total_viewer
 * @property        int              $allow_feed
 * @property        int              $to_story
 * @property        ?string[]        $tagged_friends
 * @property        ?PlaybackData    $playback
 * @property        string           $owner_navigation_link
 * @property        ?string          $webcam_config
 * @method   static LiveVideoFactory factory(...$parameters)
 */
class LiveVideo extends Model implements
    Content,
    ActivityFeedSource,
    AppendPrivacyList,
    HasPrivacy,
    HasResourceStream,
    HasApprove,
    HasTaggedFriend,
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
    HasLocationCheckin,
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
    use HasTaggedFriendTrait;

    public const ENTITY_TYPE = 'live_video';

    /** @var array<mixed> */
    public $nestedAttributes = [
        'playback',
        'liveVideoText' => ['text', 'text_parsed'],
    ];

    protected $table = 'livestreaming_live_videos';

    /** @var string[] */
    protected $fillable = [
        'title',
        'stream_key',
        'duration',
        'asset_id',
        'live_stream_id',
        'live_type',
        'is_streaming',
        'is_landscape',
        'module_id',
        'package_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'is_featured',
        'featured_at',
        'is_sponsor',
        'sponsor_in_feed',
        'tags',
        'total_like',
        'total_share',
        'total_view',
        'total_attachment',
        'image_file_id',
        'is_approved',
        'location_latitude',
        'location_longitude',
        'location_name',
        'created_at',
        'updated_at',
        'view_id',
        'last_ping',
        'total_viewer',
        'allow_feed',
        'tagged_friends',
        'total_tag_friend',
        'total_pending_reply',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'to_story',
        'location_address',
        'webcam_config',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_approved'     => 'boolean',
        'is_sponsor'      => 'boolean',
        'sponsor_in_feed' => 'boolean',
        'is_featured'     => 'boolean',
        'tags'            => 'array',
        'tagged_friends'  => 'array',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    /**
     * @return LiveVideoFactory
     */
    protected static function newFactory(): LiveVideoFactory
    {
        return LiveVideoFactory::new();
    }

    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'livestreaming_tag_data',
            'item_id',
            'tag_id'
        )->using(LiveVideoTagData::class);
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(LiveVideoPrivacyStream::class, 'item_id', 'id');
    }

    public function liveVideoText(): HasOne
    {
        return $this->hasOne(LiveVideoText::class, 'id', 'id');
    }

    public function getPolicy(): ?ResourcePolicyInterface
    {
        return resolve(LiveVideoPolicy::class);
    }

    public function playback(): HasOne
    {
        return $this->hasOne(PlaybackData::class, 'live_id', 'id');
    }

    public function toTitle(): string
    {
        return ban_word()->clean($this->title) ?: __p('livestreaming::phrase.live_video_label_saved');
    }

    public function toNotificationTitle(): string
    {
        return ban_word()->clean($this->title);
    }

    /**
     * @return array<int, mixed>
     */
    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function playbackData(): HasMany
    {
        return $this->hasMany(PlaybackData::class, 'id', 'id');
    }

    public function toActivityFeed(): ?FeedAction
    {
        if (!$this->isApproved()) {
            return null;
        }

        if (!$this->allow_feed) {
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
            'content'    => $this->liveVideoText?->text,
            'privacy'    => $this->privacy,
        ]);
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => ban_word()->clean($this->title) ?: __p('livestreaming::phrase.live_video_label_saved'),
            'image'          => $this->images,
            'item_type_name' => __p("livestreaming::phrase.{$this->entityType()}_label_saved"),
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

        $text = $this->liveVideoText;

        return [
            'title' => ban_word()->clean($this->title),
            'text'  => $text ? ban_word()->clean($text->text_parsed) : '',
        ];
    }

    public function moduleName(): string
    {
        return 'livestreaming';
    }

    /**
     * @throws AuthenticationException
     */
    public function isOffNotification(): bool
    {
        $user  = user();
        $owner = $this->user;

        return resolve(NotificationSettingRepositoryInterface::class)->isTurnOffNotify($owner, $user);
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("live-video/{$this->entityId()}");
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("live-video/{$this->entityId()}");
    }

    public function toLocationObject(): array
    {
        return [
            'address'      => $this->location_name,
            'lat'          => $this->location_latitude,
            'lng'          => $this->location_longitude,
            'full_address' => $this->location_address,
        ];
    }

    public function buildSeoData(): array
    {
        if (!empty($this->title)) {
            return [];
        }

        $title = __p('livestreaming::phrase.live_video');

        return [
            'title'    => $title,
            'og:title' => $title,
        ];
    }

    /**
     * @param  UserEntity $user
     * @param  UserEntity $owner
     * @param  bool       $isMention
     * @return string
     */
    public function toCallbackMessage(UserEntity $user, UserEntity $owner, bool $isMention = false): string
    {
        $yourName = $user->name;

        return __p('livestreaming::notification.username_tagged_you_in_a_live_video', [
            'username' => $yourName,
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    public function toFeedOGImages(): array
    {
        $thumbnails = $this->images;

        return is_array($thumbnails) ? $thumbnails : [];
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new LiveVideoApproveNotification($this)];
    }

    public function toSponsorData(): ?array
    {
        $title = $this->toNotificationTitle();

        return [
            'title' => __p('livestreaming::phrase.sponsor_title', [
                'title'   => $title,
                'isTitle' => !empty($title),
                'id'      => $this->entityId(),
            ]),
        ];
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        $this->loadMissing('liveVideoText');
        $videoText = $this->liveVideoText;

        if (!$videoText instanceof LiveVideoText) {
            return null;
        }

        return strip_tags($videoText->text_parsed);
    }

    public function toFollowerCallbackMessage(?string $locale = null): ?string
    {
        if (!$this->owner instanceof HasPrivacyMember) {
            return null;
        }

        return __p('livestreaming::notification.user_full_name_started_a_live_video_in_owner', [
            'user_full_name' => $this->user->toTitle(),
            'owner_type'     => __p_type_key($this->ownerType()),
            'owner_name'     => $this->ownerEntity->name,
        ], $locale);
    }

    public function getOwnerNavigationLinkAttribute(): ?string
    {
        $owner = $this->owner;

        if (!$owner instanceof ContractUser) {
            return null;
        }

        if ($owner->entityType() === 'user') {
            return '/live-video';
        }

        $hasParentLink = $owner->has_live_video_parent_link;

        if (true !== $hasParentLink) {
            return null;
        }

        return sprintf('%s/live-video', $owner->toLink());
    }

    public function toFeaturedData(): ?array
    {
        return [];
    }
}
