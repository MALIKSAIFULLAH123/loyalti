<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;
use MetaFox\Photo\Contracts\HasTotalPhoto;
use MetaFox\Photo\Database\Factories\PhotoGroupFactory;
use MetaFox\Photo\Notifications\NewPhotoToFollowerNotification;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Platform\Contracts\ActivityFeedSourceCanEditAttachment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasSponsorInFeed;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\ResourcePostOnOwner;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class PhotoGroup.
 *
 * @mixin Builder
 * @property        int                  $id
 * @property        int                  $album_id
 * @property        int                  $total_item
 * @property        string               $content
 * @property        string               $created_at
 * @property        string               $updated_at
 * @property        Collection           $items
 * @property        ?Album               $album
 * @property        string               $album_name
 * @property        string               $album_link
 * @property        ?CollectionStatistic $statistic
 * @method   static PhotoGroupFactory    factory()
 */
class PhotoGroup extends Model implements
    Content,
    ResourcePostOnOwner,
    ActivityFeedSourceCanEditAttachment,
    AppendPrivacyList,
    HasPrivacy,
    HasFeature,
    HasSponsor,
    HasSponsorInFeed,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasTotalView,
    HasTotalPhoto,
    HasTotalItem,
    HasLocationCheckin,
    HasSavedItem,
    HasTaggedFriend,
    HasApprove
{
    use HasContent;
    use HasFactory;
    use HasAmountsTrait;
    use HasUserMorph;
    use HasOwnerMorph;
    use AppendPrivacyListTrait;
    use HasTaggedFriendTrait;

    public const ENTITY_TYPE = 'photo_set';

    public const PHOTO_ALBUM_UPDATE_TYPE = 'update_photo_album';

    public const FEED_POST_TYPE = self::ENTITY_TYPE;

    protected $fillable = [
        'album_id',
        'total_item',
        'content',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'total_view',
        'total_like',
        'total_share',
        'location_name',
        'location_latitude',
        'location_longitude',
        'is_featured',
        'is_sponsor',
        'is_approved',
        'total_tag_friend',
        'total_pending_reply',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'created_at',
        'updated_at',
        'location_address',
    ];

    /**
     * @var string[]
     */
    protected $with = ['statistic'];

    /**
     * @var string[]
     */
    protected $appends = [
        'album_name',
        'album_link',
    ];

    protected static function newFactory(): PhotoGroupFactory
    {
        return PhotoGroupFactory::new();
    }

    public function toActivityFeed(): ?FeedAction
    {
        if (null === $this->user) {
            return null;
        }

        if (!$this->items()->count()) {
            return null;
        }

        if ($this->processingItems()->count()) {
            return null;
        }

        /**
         * type_id of feed shall be either: 'photo_set' or 'update_photo_album'
         * photo_set: when not belongs to any album
         * update_photo_album: when belongs to a normal album.
         */
        $typeId  = $this->entityType();
        $content = $this->getFeedContent();

        $this->loadMissing(['album']);

        if ($this->album instanceof Album && $this->album->album_type == Album::NORMAL_ALBUM) {
            $typeId  = self::PHOTO_ALBUM_UPDATE_TYPE;
            $content = '';
        }

        return new FeedAction([
            'user_id'    => $this->userId(),
            'user_type'  => $this->userType(),
            'owner_id'   => $this->ownerId(),
            'owner_type' => $this->ownerType(),
            'item_id'    => $this->entityId(),
            'item_type'  => $this->entityType(),
            'type_id'    => $typeId,
            'privacy'    => $this->privacy,
            'content'    => $content,
            'status'     => $this->isApproved() ? MetaFoxConstant::ITEM_STATUS_APPROVED : MetaFoxConstant::ITEM_STATUS_PENDING,
        ]);
    }

    public function getFeedContent(): ?string
    {
        return $this->content;
    }

    public function items(): HasMany
    {
        return $this->hasMany(PhotoGroupItem::class, 'group_id', 'id')
            ->orderBy('ordering')
            ->orderBy('id');
    }

    public function getMediaItemsAttribute()
    {
        $limit             = $this->getMediaItemsLimit();
        $isViewPendingFeed = $this->isViewPendingFeed();

        return LoadReduce::get(sprintf('PhotoGroup:items(%s)', $this->id), function () use ($limit, $isViewPendingFeed) {
            $items = $this->items;

            if (!$items->count()) {
                return [$items, 0, 0];
            }

            $items->loadMissing(['detail']);

            if (!$isViewPendingFeed) {
                $items = $items->filter(function (PhotoGroupItem $item) {
                    return $item->isApproved();
                })->values();
            }

            $total  = $items->count();
            $items  = $items->take($limit);
            $remain = $total - $items->count();

            return [$items, $total, $remain];
        });
    }

    protected function getMediaItemsLimit(): int
    {
        return 4;
    }

    protected function isViewPendingFeed(): bool
    {
        return (bool) request()->get('is_view_pending_feed', false);
    }

    public function getAlbumAttribute()
    {
        return LoadReduce::getEntity('photo_album', $this->album_id, fn () => $this->getRelationValue('album'));
    }

    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function toSavedItem(): array
    {
        $photos = $this->items;
        /** @var Photo $firstPhoto */
        $firstPhoto = $photos->first();

        $title        = $this->toSavedItemTitle();
        $itemTypeName = $this->toSaveItemTypeName();

        return $this->buildSavedItemArray($title, $firstPhoto?->detail?->images, $itemTypeName);
    }

    public function toSavedItemWithContext(ContractUser $context): array
    {
        $title        = $this->toSavedItemTitle();
        $itemTypeName = $this->toSaveItemTypeName();

        return $this->buildSavedItemArray($title, $this->getSavedItemImages($context), $itemTypeName);
    }

    protected function getSavedItemImages(ContractUser $context): ?array
    {
        return $this->getImagesFromFirstPhoto($context) ?? $this->getImagesFromFirstVideo();
    }

    protected function getImagesFromFirstPhoto(ContractUser $context): ?array
    {
        if (!$this->hasPhoto()) {
            return null;
        }

        $photos = Photo::query()
            ->join('photo_group_items', 'photos.id', '=', 'photo_group_items.item_id')
            ->where('photo_group_items.group_id', $this->id)
            ->where('photo_group_items.item_type', Photo::ENTITY_TYPE)
            ->orderBy('photos.mature')
            ->orderBy('photos.ordering')
            ->orderBy('photos.id')
            ->select('photos.*')
            ->get();

        $firstPhoto = null;

        foreach ($photos as $photoItem) {
            if (policy_check(PhotoPolicy::class, 'viewMature', $context, $photoItem)) {
                $firstPhoto = $photoItem;
                break;
            }
        }

        return $firstPhoto?->images;
    }

    protected function getImagesFromFirstVideo(): ?array
    {
        if (!$this->hasVideo()) {
            return null;
        }

        $firstVideo = $this->items()
            ->whereNot('item_type', Photo::ENTITY_TYPE)
            ->orderBy('photo_group_items.id')
            ->first();

        return $firstVideo?->detail?->images;
    }

    public function hasPhoto(): bool
    {
        return (bool) $this->statistic?->total_photo;
    }

    public function hasVideo(): bool
    {
        return (bool) $this->statistic?->total_video;
    }

    protected function toSavedItemTitle(): string
    {
        $title = $this->getFeedContent();

        if (is_string($title) && MetaFoxConstant::EMPTY_STRING !== $title) {
            return $title;
        }

        $photoStatistic = $this->statistic()->getResults();

        $totalPhoto = $photoStatistic->total_photo;

        $hasPhoto = $totalPhoto > 0 ? '1' : '0';

        $totalVideo = $photoStatistic->total_video;

        $hasVideo = $totalVideo > 0 ? '1' : '0';

        return __p('photo::phrase.n_photos_and_m_videos', [
            'has_photo'    => $hasPhoto,
            'has_video'    => $hasVideo,
            'total_photos' => $totalPhoto,
            'total_videos' => $totalVideo,
        ]);
    }

    private function buildSavedItemArray(string $title, ?array $image, string $itemTypeName): array
    {
        return [
            'title'          => $title,
            'image'          => $image,
            'item_type_name' => $itemTypeName,
            'total_photo'    => $this->statistic?->total_photo ?? 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    protected function toSaveItemTypeName(): string
    {
        return __p('photo::phrase.media_label_saved');
    }

    public function toLink(): ?string
    {
        $feed = $this->activity_feed;

        if ('feed' === $feed?->entityType()) {
            return url_utility()->makeApiResourceUrl($feed->entityType(), $feed->entityId());
        }

        return null;
    }

    public function toTitle(): string
    {
        return $this->toSavedItemTitle();
    }

    public function toUrl(): ?string
    {
        $feed = $this->activity_feed;

        if ('feed' === $feed?->entityType()) {
            return url_utility()->makeApiResourceFullUrl($feed->entityType(), $feed->entityId());
        }

        return null;
    }

    public function toRouter(): ?string
    {
        $feed = $this->activity_feed;

        if ('feed' === $feed?->entityType()) {
            return url_utility()->makeApiMobileResourceUrl($feed->entityType(), $feed->entityId());
        }

        return null;
    }

    /**
     * @return BelongsTo
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function statistic(): MorphOne
    {
        return $this->morphOne(CollectionStatistic::class, 'statistic', 'item_type', 'item_id');
    }

    public function getAlbumNameAttribute(): string
    {
        return ban_word()->clean($this->album?->name ?? '');
    }

    public function getAlbumLinkAttribute(): string
    {
        return $this->album?->toLink() ?? '';
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

    public function pendingItems(): HasMany
    {
        return $this->hasMany(PhotoGroupItem::class, 'group_id', 'id')
            ->whereHas('detail', function (Builder $subQuery) {
                return $subQuery->where('is_approved', 0);
            })
            ->orderBy('ordering')
            ->orderBy('id');
    }

    public function approvedItems(): HasMany
    {
        return $this->hasMany(PhotoGroupItem::class, 'group_id', 'id')
            ->whereHas('detail', function (Builder $subQuery) {
                return $subQuery->where('is_approved', 1);
            })
            ->orderBy('ordering')
            ->orderBy('id');
    }

    public function processingItems(): HasMany
    {
        return $this->hasMany(PhotoGroupItem::class, 'group_id', 'id')
            ->whereHas('detail', function (Builder $subQuery) {
                return $subQuery->where('in_process', 1);
            });
    }

    public function toFollowerNotification(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        $user = $this->user;
        if (!$user) {
            return null;
        }

        $message = __p('photo::notification.user_name_posted_a_post', [
            'user_name' => $user->full_name,
        ]);

        $notification = new NewPhotoToFollowerNotification();

        Arr::set($data, 'type', $notification->getType());

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$user],
            'type'    => $notification->getType(),
        ];
    }

    public function getKeepCommentItemRouterAttribute(): bool
    {
        return true;
    }

    /**
     * @return array<int, mixed>
     */
    public function toFeedOGImages(): array
    {
        $this->loadMissing('items');
        $items = $this->items;

        if (!$items instanceof Collection) {
            return [];
        }

        $firstItem = $items->filter(function (mixed $item) {
            if (!$item instanceof PhotoGroupItem) {
                return false;
            }
            $detail = $item->detail;

            if (!$detail->isApproved()) {
                return false;
            }

            if (property_exists($detail, 'in_process')) {
                return !$detail->in_process;
            }

            return true;
        })->first();

        $detail = $firstItem->detail;
        if (!$detail instanceof HasThumbnail) {
            return [];
        }

        $thumbnails = $detail->images;

        return is_array($thumbnails) ? $thumbnails : [];
    }

    /**
     * @param string $itemType
     * @param int    $amount
     *
     * @return int
     */
    public function increaseStatisticAmount(string $itemType, int $amount = 1): int
    {
        $result = $this->incrementAmount('total_item', $amount);

        if ($this->statistic instanceof CollectionStatistic) {
            $this->statistic->incrementTotalColumn($itemType);
        }

        return $result;
    }

    public function decreaseStatisticAmount(string $itemType, int $amount = 1): int
    {
        $result = $this->decrementAmount('total_item', $amount);

        if ($this->statistic instanceof CollectionStatistic) {
            $this->statistic->decrementTotalColumn($itemType);
        }

        return $result;
    }

    public function getForceSaveItemAttribute(): bool
    {
        return false;
    }

    public function isSingleItemPhotoGroup(): bool
    {
        if ($this->total_item != 1) {
            return false;
        }

        $groupItem = $this->getFirstApprovedPhotoGroupItem();

        if (!$groupItem instanceof PhotoGroupItem) {
            return false;
        }

        return $groupItem->detail instanceof Media;
    }

    public function getFirstApprovedPhotoGroupItem(?int $ignoreItemId = null): ?PhotoGroupItem
    {
        $query = $this->items()
            ->where('photo_group_items.is_approved', 1);

        if (!empty($ignoreItemId)) {
            $query->whereNot('photo_group_items.item_id', $ignoreItemId);
        }

        return $query->orderBy('photo_group_items.item_id')->first();
    }
}
