<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Story\Database\Factories\StoryFactory;
use MetaFox\Story\Notifications\NewStoryToFollowerNotification;
use MetaFox\Story\Support\StorySupport;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Story.
 *
 * @property        int                  $id
 * @property        int                  $in_process
 * @property        int                  $is_favorite
 * @property        bool                 $is_archive
 * @property        bool                 $is_publish
 * @property        int                  $set_id
 * @property        int                  $view_id
 * @property        int                  $user_id
 * @property        string               $user_type
 * @property        int                  $owner_id
 * @property        string               $owner_type
 * @property        int                  $privacy
 * @property        int                  $image_file_id
 * @property        int                  $thumbnail_file_id
 * @property        int                  $video_file_id
 * @property        int                  $background_id
 * @property        int                  $duration
 * @property        int                  $total_comment
 * @property        int                  $total_reply
 * @property        int                  $total_like
 * @property        int                  $total_share
 * @property        int                  $total_view
 * @property        int                  $total_attachment
 * @property        int                  $total_play
 * @property        int                  $total_pending_comment
 * @property        int                  $total_pending_reply
 * @property        int                  $total_tag_friend
 * @property        int                  $is_approved
 * @property        int                  $expired_at
 * @property        int                  $item_id
 * @property        string               $item_type
 * @property        string               $created_at
 * @property        string               $type
 * @property        string               $asset_id
 * @property        string               $updated_at
 * @property        mixed                $extra
 * @property        StoryText|null       $storyText
 * @property        StoryView            $viewers
 * @property        StoryReaction        $reactions
 * @property        StoryBackground|null $storyBackground
 * @property        array|null           $thumbnails
 * @property        string|null          $video
 * @property        string|null          $destination
 * @property        string|null          $expand_link
 * @property        array|null           $default_images
 * @property        StorySet             $storySet
 * @property        bool                 $is_ready
 * @property        bool                 $is_processing
 * @property        bool                 $is_process_failed
 * @method   static StoryFactory         factory(...$parameters)
 */
class Story extends Model implements
    Content,
    HasTotalView,
    AppendPrivacyList,
    HasResourceStream,
    HasPrivacy,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasThumbnail,
    HasHashTag
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasFactory;
    use HasNestedAttributes;
    use HasThumbnailTrait;
    use HasTotalAttachmentTrait;
    use AppendPrivacyListTrait;

    public const ENTITY_TYPE = 'story';

    protected $table = 'stories';

    /** @var string[] */
    protected $fillable = [
        'in_process',
        'is_favorite',
        'is_archive',
        'is_publish',
        'set_id',
        'view_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'type',
        'extra',
        'asset_id',
        'image_file_id',
        'thumbnail_file_id',
        'video_file_id',
        'background_id',
        'duration',
        'destination',
        'expired_at',
        'total_comment',
        'total_reply',
        'total_like',
        'total_share',
        'total_view',
        'total_attachment',
        'total_play',
        'total_pending_comment',
        'total_pending_reply',
        'total_tag_friend',
        'is_approved',
        'item_id',
        'item_type',
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_archive' => 'boolean',
        'extra'      => 'array',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id'     => 'photo',
        'thumbnail_file_id' => 'photo',
        'video_file_id'     => 'video',
    ];

    /**
     * @return StoryFactory
     */
    protected static function newFactory()
    {
        return StoryFactory::new();
    }

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'storyText' => ['text', 'text_parsed'],
    ];

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'story_tag_data',
            'item_id',
            'tag_id'
        )->using(StoryTagData::class);
    }

    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PrivacyStream::class, 'item_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function storyText(): HasOne
    {
        return $this->hasOne(StoryText::class, 'id', 'id');
    }

    public function storySet(): BelongsTo
    {
        return $this->belongsTo(StorySet::class, 'set_id', 'id');
    }

    public function viewers(): HasMany
    {
        return $this->hasMany(StoryView::class, 'story_id', 'id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(StoryReaction::class, 'story_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function storyBackground(): HasOne
    {
        return $this->hasOne(StoryBackground::class, 'id', 'background_id');
    }

    public function toTitle(): string
    {
        return $this->storyText?->text ?? __p('story::phrase.story');
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("{$this->entityType()}/{$this->userId()}/{$this->entityId()}");
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("{$this->entityType()}/{$this->userId()}/{$this->entityId()}");
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl("{$this->entityType()}/{$this->userId()}/{$this->entityId()}");
    }

    public function toFollowerNotification(): ?array
    {
        if (!$this->is_ready) {
            return null;
        }

        $message = __p('story::notification.user_create_new_story', [
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewStoryToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
            'type'    => $notification->getType(),
        ];
    }

    public function getThumbnailsAttribute(): ?array
    {
        $thumbnailId = $this->thumbnail_file_id;

        if (!empty($this->extra) && !$thumbnailId) {
            return Arr::get($this->extra, 'image');
        }

        return app('storage')->getUrls($thumbnailId ?? 0);
    }

    public function getDefaultImagesAttribute(): ?array
    {
        $assetId = app('asset')->findByName('story_no_image')?->file_id;

        return $this->image_file_id ? app('storage')->getUrls($this->image_file_id) : app('storage')->getUrls($assetId);
    }

    public function getVideoAttribute(): ?string
    {
        $videoId = $this->video_file_id;

        if (!$videoId && !empty($this->extra) && Arr::has($this->extra, 'video')) {
            return Arr::get($this->extra, 'video');
        }

        return $videoId ? app('storage')->getUrl($this->video_file_id) : $this->destination;
    }

    public function getIsReadyAttribute(): bool
    {
        return StorySupport::STATUS_VIDEO_READY === $this->in_process && $this->is_publish;
    }

    public function getIsProcessingAttribute(): bool
    {
        if (!$this->is_publish) {
            return true;
        }

        return StorySupport::STATUS_VIDEO_PROCESS === $this->in_process;
    }

    public function getIsProcessFailedAttribute(): bool
    {
        return StorySupport::STATUS_VIDEO_FAILED === $this->in_process;
    }

    public function getImagesAttribute(): ?array
    {
        $thumbnail = $this->getThumbnail();

        if (null === $thumbnail) {
            return null;
        }

        if (!empty($this->extra) && !$thumbnail) {
            return Arr::get($this->extra, 'image');
        }

        return app('storage')->getUrls($this->getThumbnail());
    }

    public function isArchived(): bool
    {
        return $this->is_archive;
    }

    public function getExpandLinkAttribute(): ?string
    {
        return Arr::get($this->extra, 'expand_link');
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at < now()->timestamp;
    }
}

// end
