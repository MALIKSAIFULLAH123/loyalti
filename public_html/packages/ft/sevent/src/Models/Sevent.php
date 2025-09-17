<?php

namespace Foxexpert\Sevent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Foxexpert\Sevent\Database\Factories\SeventFactory;
use Foxexpert\Sevent\Notifications\SeventApproveNotification;
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

use function PHPSTORM_META\map;

class Sevent extends Model implements
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

    public const ENTITY_TYPE = 'sevent';

    public const RADIO_STATUS_PUBLIC = 1;
    public const RADIO_STATUS_DRAFT = 2;

    public array $fileColumns = [
        'image_file_id' => 'photo',
        'host_image_file_id' => 'photo'
    ];
    protected $appends = ['host'];

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
        'start_date'         => 'datetime',
        'end_date'           => 'datetime'
    ];

    public function getHostAttribute()
    {
        if (empty($this->host_image_file_id)) return null;

        $file = upload()->getFile($this->host_image_file_id, true);
        
        return Storage::url($file->path);
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
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Image::class, 'sevent_id', 'id')
            ->orderBy('ordering');
    }

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'categories',
        'seventText' => ['text', 'text_parsed'],
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'host_image_file_id',
        'is_host',
        'host_title',
        'host_contact',
        'host_website',
        'host_facebook',
        'host_description',
        'course_id',

        'title',
        'module_id',
        'image_file_id',
        'user_id',
        'audio_link',
        'user_type',
        'owner_id',
        'start_date',
        'end_date',
        'is_online',
        'total_attending',
        'total_interested',
        'online_link',
        'owner_type',
        'privacy',
        'is_draft',
        'short_description',
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
        'image',
        'terms',
        'is_unlimited',
        'video',
        'location_name',
        'location_latitude',
        'location_longitude',
        'country_iso',
    ];

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'sevent_tag_data',
            'item_id',
            'tag_id'
        )->using(SeventTagData::class);
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'sevent_category_data',
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
            'sevent_category_data',
            'item_id',
            'category_id'
        )->where('is_active', Category::IS_ACTIVE)->using(CategoryData::class);
    }

    /**
     * @return HasOne
     */
    public function seventText(): HasOne
    {
        return $this->hasOne(SeventText::class, 'id', 'id');
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

    protected static function newFactory(): SeventFactory
    {
        return SeventFactory::new();
    }

    public function toSavedItem(): array
    {
        return [
            'title'          => $this->title,
            'source'          => $this->source,
            'source_type'          => $this->source_type,
            'image'          => $this->image,
            'item_type_name' => __p("sevent::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => $this->getThumbnail() ? 1 : 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toSearchable(): ?array
    {
        // A draft sevent is not allowed to be searched
        if ($this->isDraft()) {
            return null;
        }

        if (!$this->isApproved()) {
            return null;
        }

        $text = $this->seventText;

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
        return strip_tags(substr($this->seventText?->text_parsed, 0, 500));
    }

    public function toTitle(): string
    {
        return Arr::get($this->attributes, 'title', MetaFoxConstant::EMPTY_STRING);
    }

    public function toApprovedNotification(): array
    {
        return [$this->user, new SeventApproveNotification($this)];
    }

    public function toSponsorData(): ?array
    {
        return [
            'title' => __p('sevent::phrase.sponsor_title', [
                'title' => $this->toTitle(),
            ]),
        ];
    }

    public function toFollowerNotification(): ?array
    {
        if ($this->isDraft()) {
            return null;
        }

        $message = __p('sevent::phrase.user_create_new_sevent_title', [
            'title'     => $this->toTitle(),
            'isTitle'   => (int)!empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
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
}
