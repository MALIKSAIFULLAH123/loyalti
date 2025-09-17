<?php

namespace MetaFox\Activity\Models;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Database\Factories\ShareFactory;
use MetaFox\Activity\Notifications\NewShareToFollowerNotification;
use MetaFox\Activity\Notifications\ShareFeedNotification;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\ResourcePostOnOwner;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Share.
 *
 * @property        int          $id
 * @property        int          $user_id
 * @property        string       $user_type
 * @property        int          $owner_id
 * @property        string       $owner_type
 * @property        int          $item_id
 * @property        string       $item_type
 * @property        int          $parent_feed_id
 * @property        string       $parent_module_id
 * @property        string       $content
 * @property        int          $total_view
 * @property        int          $privacy
 * @property        string       $created_at
 * @property        string       $updated_at
 * @property        string       $context_item_type
 * @property        int          $context_item_id
 * @property        Content|null $context_item
 * @property        string|null  $location_address
 * @method   static ShareFactory factory(...$parameters)
 */
class Share extends Model implements
    Content,
    ActivityFeedSource,
    HasPrivacy,
    AppendPrivacyList,
    ResourcePostOnOwner,
    HasTaggedFriend,
    HasTotalView,
    HasTotalLike,
    HasTotalShare,
    HasTotalCommentWithReply,
    HasLocationCheckin,
    HasApprove,
    HasSavedItem,
    IsNotifyInterface
{
    use HasContent;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasItemMorph;
    use AppendPrivacyListTrait;
    use HasTaggedFriendTrait;

    public const ENTITY_TYPE = 'share';

    public const IMPORT_ENTITY_TYPE = 'activity_share';

    public const FEED_POST_TYPE = self::ENTITY_TYPE;

    protected $table = 'activity_shares';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'item_id',
        'item_type',
        'parent_feed_id',
        'parent_module_id',
        'content',
        'total_view',
        'total_like',
        'total_share',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'total_pending_reply',
        'total_tag_friend',
        'privacy',
        'location_latitude',
        'location_longitude',
        'location_name',
        'is_approved',
        'context_item_type',
        'context_item_id',
        'location_address',
    ];

    /**
     * @return ShareFactory
     */
    protected static function newFactory(): ShareFactory
    {
        return ShareFactory::new();
    }

    public function toActivityFeed(): ?FeedAction
    {
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

    public function toNotification(): ?array
    {
        $userItem = $this->item->user;

        $owner = $this->owner;

        if (null === $userItem) {
            return null;
        }

        if (null === $owner) {
            return null;
        }

        if ($userItem->entityId() == $owner->entityId()) {
            return null;
        }

        if ($userItem->entityId() == $this->userId()) {
            return null;
        }

        if (!PrivacyPolicy::checkPermissionOwner($userItem, $owner)) {
            return null;
        }

        if (!PrivacyPolicy::checkPermission($userItem, $this)) {
            return null;
        }

        if (!$userItem instanceof HasPrivacyMember) {
            return [$userItem, new ShareFeedNotification($this)];
        }

        if ($userItem->userId() == $owner->entityId()) {
            return null;
        }

        return [$userItem->user, new ShareFeedNotification($this)];
    }

    public function getFeedContent(): ?string
    {
        return $this->content;
    }

    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    public function toSearchable(): ?array
    {
        if (!$this->isApproved()) {
            return null;
        }

        return [
            'title' => $this->getFeedContent(),
            'text'  => $this->getFeedContent(),
        ];
    }

    public function toTitle(): string
    {
        if (!empty($this->content)) {
            return ban_word()->clean($this->content);
        }

        if ($this->item instanceof Content) {
            return ban_word()->clean($this->item->toTitle());
        }

        return MetaFoxConstant::EMPTY_STRING;
    }

    /**
     * @throws AuthenticationException
     */
    public function toLink(): ?string
    {
        $feed = $this->activity_feed;

        if (!resolve(TypeManager::class)->hasFeature($this->entityType(), Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
            return $feed?->toLink();
        }

        if ($feed instanceof Feed) {
            return url_utility()->makeApiResourceUrl($feed->entityType(), $feed->entityId());
        }

        return $this->item?->toLink();
    }

    /**
     * @throws AuthenticationException
     */
    public function toUrl(): ?string
    {
        $feed = $this->activity_feed;

        if (!resolve(TypeManager::class)->hasFeature($this->entityType(), Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
            return $feed?->toUrl();
        }

        if ($feed instanceof Feed) {
            return url_utility()->makeApiResourceFullUrl($feed->entityType(), $feed->entityId());
        }

        return $this->item?->toUrl();
    }

    /**
     * @throws AuthenticationException
     */
    public function toRouter(): ?string
    {
        $feed = $this->activity_feed;

        if (!resolve(TypeManager::class)->hasFeature($this->entityType(), Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
            return $feed?->toRouter();
        }

        if ($feed instanceof Feed) {
            return url_utility()->makeApiMobileResourceUrl($feed->entityType(), $feed->entityId());
        }

        return $this->item?->toRouter();
    }

    public function toSavedItem(): array
    {
        $title = ($this->getFeedContent() && $this->getFeedContent() != '') ? $this->getFeedContent() : __p('activity::phrase.share');

        return [
            'title'          => $title,
            'image'          => null,
            'item_type_name' => __p('activity::phrase.share'),
            'total_photo'    => 0,
            'user'           => $this->userEntity,
            'link'           => $this->toLink(),
            'url'            => $this->toUrl(),
            'router'         => $this->toRouter(),
        ];
    }

    public function toFollowerNotification(): ?array
    {
        $title = $this->toTitle();
        app('events')->dispatch('core.parse_content', [$this, &$title]);

        $title = strip_tags($title);

        $message = __p('activity::notification.user_name_share_a_post', [
            'title'     => $title,
            'isTitle'   => (int) !empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        $userItem = $this->item?->user;
        $exclude  = [$userItem, $this->user];

        if ($userItem instanceof HasPrivacyMember) {
            $exclude = array_merge($exclude, [$userItem->user]);
        }

        $notification = new NewShareToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => $exclude,
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
        $this->loadMissing('item');
        if (null === $this->item) {
            return [];
        }

        if ($this->item instanceof Feed) {
            return $this->item->toOGImages();
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
        $this->loadMissing('item');
        if (null === $this->item) {
            return null;
        }

        if (!method_exists($this->item, 'toOGDescription')) {
            return null;
        }

        return $this->item->toOGDescription($context);
    }

    public function contextItem(): ?MorphTo
    {
        try {
            return $this->morphTo('context_item', 'context_item_type', 'context_item_id');
        } catch (\Throwable $throwable) {
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function contextItemType(): ?string
    {
        return $this->context_item_type;
    }

    /**
     * @return int|null
     */
    public function contextItemId(): ?int
    {
        return $this->context_item_id;
    }

    public function getContextItemAttribute()
    {
        return LoadReduce::getEntity($this->contextItemType(), $this->contextItemId(), fn () => $this->getRelationValue('contextItem'));
    }
}
