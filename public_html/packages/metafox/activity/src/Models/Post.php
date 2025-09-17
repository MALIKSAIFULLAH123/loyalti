<?php

namespace MetaFox\Activity\Models;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Database\Factories\PostFactory;
use MetaFox\Activity\Notifications\NewPostToFollowerNotification;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Activity\Traits\HasTagTrait;
use MetaFox\Platform\Contracts\ActivityFeedForm;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasBackGroundStatus;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasBackGroundStatusTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasTaggedFriendTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;

/**
 * Class Post.
 *
 * @mixin Builder
 * @property        int           $id
 * @property        int           $privacy
 * @property        int           $privacy_id
 * @property        int           $user_id
 * @property        string        $user_type
 * @property        int           $owner_id
 * @property        string        $owner_type
 * @property        string        $content
 * @property        int           $location_latitude
 * @property        int           $location_longitude
 * @property        int           $status_background_id
 * @property        string        $location_name
 * @property        string|Carbon $created_at
 * @property        string|Carbon $updated_at
 * @property        bool          $close_map_on_feed
 * @property        bool          $show_map_on_feed
 * @property        string|null   $location_address
 * @method   static PostFactory   factory(...$parameters)
 */
class Post extends Model implements
    ActivityFeedSource,
    Content,
    AppendPrivacyList,
    HasLocationCheckin,
    ActivityFeedForm,
    HasPrivacy,
    HasTotalLike,
    HasTotalCommentWithReply,
    HasTotalShare,
    HasBackGroundStatus,
    HasTaggedFriend,
    HasSavedItem,
    HasApprove
{
    use HasContent;
    use HasUserMorph;
    use HasOwnerMorph;
    use AppendPrivacyListTrait;
    use HasFactory;
    use HasTaggedFriendTrait;
    use HasTagTrait;
    use HasBackGroundStatusTrait;

    protected $table = 'activity_posts';

    public const ENTITY_TYPE = 'activity_post';

    public const FEED_POST_TYPE = self::ENTITY_TYPE;

    protected $fillable = [
        'privacy',
        'privacy_id',
        'user_id',
        'user_type',
        'content',
        'total_like',
        'total_share',
        'total_comment',
        'total_reply',
        'total_pending_comment',
        'total_pending_reply',
        'total_tag_friend',
        'location_latitude',
        'location_longitude',
        'location_name',
        'owner_id',
        'owner_type',
        'status_background_id',
        'is_approved',
        'updated_at',
        'created_at',
        'close_map_on_feed',
        'location_address',
    ];

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

    public function toLocation(): array
    {
        return [$this->location_name, $this->location_latitude, $this->location_longitude, null, $this->location_address];
    }

    /**
     * @return PostFactory
     */
    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function getFeedContent(): ?string
    {
        return $this->content;
    }

    /**
     * @deprecated Remove in 5.1.13
     * @return array<string, mixed>|null
     */
    public function getBackgroundStatusImage(): ?array
    {
        return ActivityFeed::getBackgroundStatusImage($this->status_background_id);
    }

    /**
     * @throws AuthenticationException
     */
    public function toSavedItem(): array
    {
        $user = $this->userEntity;

        return [
            'title'          => $this->getFeedContent() ?: __p('activity::phrase.activity_post_label_saved'),
            'image'          => $user instanceof UserEntity ? $user->avatars : null,
            'item_type_name' => __p("activity::phrase.{$this->entityType()}_label_saved"),
            'total_photo'    => 0,
            'user'           => $user,
            'link'           => $this?->toLink(),
            'url'            => $this?->toUrl(),
            'router'         => $this?->toRouter(),
        ];
    }

    /**
     * @return string
     */
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

        return '';
    }

    /**
     * @throws AuthenticationException
     */
    public function toLink(): ?string
    {
        $feed = $this->activity_feed;

        if (resolve(TypeManager::class)->hasFeature($this->entityType(), Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
            return url_utility()->makeApiResourceUrl($feed?->entityType(), $feed?->entityId());
        }

        return $feed?->toLink();
    }

    /**
     * @throws AuthenticationException
     */
    public function toUrl(): ?string
    {
        $feed = $this->activity_feed;

        if (resolve(TypeManager::class)->hasFeature($this->entityType(), Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
            return url_utility()->makeApiResourceFullUrl($feed?->entityType(), $feed?->entityId());
        }

        return $feed?->toUrl();
    }

    /**
     * @throws AuthenticationException
     */
    public function toRouter(): ?string
    {
        $feed = $this->activity_feed;
        if (resolve(TypeManager::class)->hasFeature($this->entityType(), Type::CAN_REDIRECT_TO_DETAIL_TYPE)) {
            return url_utility()->makeApiMobileResourceUrl($feed?->entityType(), $feed?->entityId());
        }

        return $feed?->toRouter();
    }

    public function getHideStatusWhenErrorViewPermissionAttribute(): bool
    {
        return false;
    }

    public function toFollowerNotification(): ?array
    {
        $content         = $this->content;

        $attributeParser = [
            'parse_url' => false,
        ];

        app('events')->dispatch('core.parse_content', [$this, &$content, $attributeParser]);

        $message = __p('activity::notification.user_name_create_a_new_post', [
            'title'     => ban_word()->clean($content),
            'isTitle'   => (int) !empty($this->toTitle()),
            'user_name' => $this->user->full_name,
        ]);

        $notification = new NewPostToFollowerNotification();

        return [
            'owner'   => $this->owner,
            'message' => $message,
            'exclude' => [$this->user],
            'type'    => $notification->getType(),
        ];
    }

    public function getKeepCommentItemRouterAttribute(): bool
    {
        return true;
    }

    public function toOGDescription(?ContractUser $context = null): ?string
    {
        return ban_word()->clean($this->content);
    }

    public function getShowMapOnFeedAttribute(): bool
    {
        if ($this->status_background_id) {
            return false;
        }

        return !$this->close_map_on_feed;
    }
}
